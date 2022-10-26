<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account;

use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AccountQueryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\PlanQueryContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountPackSwitcherContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountSubscriberContract;
use Illuminate\Support\Collection;

class AccountPackSwitcher implements AccountPackSwitcherContract
{
    protected SubscriptionApiContract $subscriptionApi;
    protected AccountSubscriberContract $subscriber;

    /**
     * Professional to switch.
     * 
     * @var ProfessionalContract
     */
    protected ProfessionalContract $professional;

    /**
     * Accounts to handle.
     * 
     * @var Collection<int, AccountContract>
     */
    protected Collection $accounts;

    /**
     * Sucessfully cancelled subscriptions.
     * 
     * @var Collection<int, SubscriptionContract>
     */
    protected Collection $cancelledSubscriptions;

    /**
     * Sucessfully subscribed subscriptions.
     * 
     * @var Collection<int, SubscriptionContract>
     */
    protected Collection $subscribedSubscriptions;

    /**
     * Pack subscription created.
     * 
     * @var SubscriptionContract
     */
    protected ?SubscriptionContract $packSubscription;

    /**
     * Injecting dependencies.
     * 
     * @param SubscriptionApiContract $subscriptionApi
     * @param AccountSubscriberContract $subscriber
     */
    public function __construct(
        SubscriptionApiContract $subscriptionApi,
        AccountSubscriberContract $subscriber
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->subscriber = $subscriber;
    }

    /**
     * Moving given professional accounts to grouped subscription pack.
     * 
     * @param ProfessionalContract $professional
     * @return bool Success state
     */
    public function toPack(ProfessionalContract $professional): bool
    {
        $this->setProfessional($professional)
            ->setAccounts($this->getAccountsToCancel())
            ->cancelAccounts();

        if (!$this->hasSuccessfullyCancelledAccounts()) return false;

        $this->setPackSubscription($this->createPackSubscription());

        if (!$this->hasCreatedPackSubscription()) return false;

        $this->updateProfessionalPackSubscription()
            ->subscribeAccounts();

        return $this->hasSuccessfullySubscribedAccounts();
    }

    /**
     * Getting accounts that should be cancelled/subscribed to pack.
     * 
     * @return Collection<int, AccountContract>
     */
    protected function getAccountsToCancel(): ?Collection
    {
        /** @var AccountQueryContract */
        $query = app()->make(AccountQueryContract::class);
        
        // Matching professional and having chargebee subscription.
        return $query->whereProfessional($this->professional)
            ->active()
            ->get()
            ->filter(fn (AccountContract $account) =>
                $account->getChargebee()?->getChargebeeSubscription()
            );
    }

    /**
     * Cancelling accounts.
     * 
     * @return static
     */
    protected function cancelAccounts(): self
    {
        $this->accounts->each(fn(AccountContract $account) =>
            $this->cancelAccount($account)
        );

        return $this;
    }

    /**
     * Cancelling given account
     * 
     * @param AccountContract $account
     * @return void
     */
    protected function cancelAccount(AccountContract $account): void    
    {
        if ($account->getChargebee()->isCancelled()):
            $this->getCancelledSubscriptions()->push($account->getChargebee()->getChargebeeSubscription());
            return;
        endif;

        if (!$account->getChargebee()->cancellable()) return;

        $subscription = $this->subscriptionApi->cancelNow($account->getChargebee()->getChargebeeSubscription(), true);

        if ($subscription) $this->getCancelledSubscriptions()->push($subscription);
    }

    /**
     * Getting pack related plan.
     * 
     * @return ?SubscriptionPlanContract
     */
    protected function getPackPlan(): ?SubscriptionPlanContract
    {
        /** @var PlanQueryContract */
        $query = app()->make(PlanQueryContract::class);

        /** @var AccountContract */
        $account = $this->accounts->first();
        $account->getChargebee()->getPlan()->isYearlyBilled() ?
            $query->beingYearly()
            : $query->beingMonthly();

        /** @var ?PlanContract */
        $plan = $query->whereGlobal()->first();
        
        return $plan?->toSubscriptionPlan();
    }

    /**
     * Creating pack subscription.
     * 
     * @return ?SubscriptionContract
     */
    protected function createPackSubscription(): ?SubscriptionContract
    {
        if (!$plan = $this->getPackPlan()) return null;

        // Creating pack subscription
        $subscription = $this->subscriptionApi->create(
            $plan,
            $this->professional->getCustomer()
        );

        if (!$subscription) return null;

        // If pack should be left as trial stop.
        if (!$this->shouldActivatePack()) return $subscription;

        // Cancelling pack subscription to end trial.
        $subscription = $this->subscriptionApi->cancelNow($subscription);

        if (!$subscription) return null;

        // Reactivating subscription
        return $this->subscriptionApi->reactivate($subscription);
    }

    /**
     * Updating professional concerning newly created pack.
     * 
     * @return static
     */
    protected function updateProfessionalPackSubscription(): self
    {
        $this->professional->chargebee_subscription_pro_pack_id = $this->packSubscription->getId();
        $this->professional->persist();

        return $this;
    }

    /**
     * Subscribing accounts to pack.
     * 
     * @return static
     */
    protected function subscribeAccounts(): self
    {
        $this->accounts->each(fn (AccountContract $account) =>
            $this->subscribeAccount($account)
        );

        return $this;
    }

    /**
     * Subscribing given account to pack.
     * 
     * @param AccountContract $account
     * @return void
     */
    protected function subscribeAccount(AccountContract $account): void
    {
        $success = $this->subscriber->usePackSubscription($account, $this->packSubscription->getId());

        if ($success) $this->getSubscribedSubscriptions()->push($this->packSubscription);
    }

    /**
     * Telling if pack should be activated when created.
     * 
     * @return bool
     */
    protected function shouldActivatePack(): bool
    {
        // If at least an account is not in trial, pack should be activated.
        return $this->accounts->first(fn (AccountContract $account) => !$account->getChargebee()->isTrial());
    }

    /**
     * Telling if accounts were successfully cancelled.
     * 
     * @return bool
     */
    protected function hasSuccessfullyCancelledAccounts(): bool
    {
        return $this->getCancelledSubscriptions()->count() === $this->accounts->count();
    }

    /**
     * Telling if accounts were successfully subscribed.
     * 
     * @return bool
     */
    protected function hasSuccessfullySubscribedAccounts(): bool
    {
        return $this->getSubscribedSubscriptions()->count() === $this->accounts->count();
    }

    /**
     * Telling if pack subscription was successfully created.
     * 
     * @return bool
     */
    protected function hasCreatedPackSubscription(): bool
    {
        return !!$this->packSubscription;
    }

    /**
     * Professional to switch.
     * 
     * @param  ProfessionalContract $professional
     * @return static
     */
    protected function setProfessional(ProfessionalContract $professional): self
    {
        $this->professional = $professional;

        return $this;
    }

    /**
     * Accounts to handle.
     * 
     * @param  Collection<int, AccountContract> $accounts
     * @return static
     */
    protected function setAccounts(Collection $accounts): self
    {
        $this->accounts = $accounts;

        return $this;
    }

    /**
     * Sucessfully cancelled subscriptions.
     * 
     * @param  Collection<int, SubscriptionContract> $cancelledSubscriptions
     * @return static
     */
    protected function setCancelledSubscriptions(Collection $cancelledSubscriptions): self
    {
        $this->cancelledSubscriptions = $cancelledSubscriptions;

        return $this;
    }

    /**
     * Sucessfully subscribed subscriptions.
     * 
     * @param  Collection<int, SubscriptionContract> $subscribedSubscriptions
     * @return static
     */
    protected function setSubscribedSubscriptions(Collection $subscribedSubscriptions): self
    {
        $this->subscribedSubscriptions = $subscribedSubscriptions;

        return $this;
    }

    /**
     * Pack subscription created.
     * 
     * @param  SubscriptionContract $packSubscription
     * @return static
     */
    protected function setPackSubscription(?SubscriptionContract $packSubscription): self
    {
        $this->packSubscription = $packSubscription;

        return $this;
    }

    /**
     * Getting cancelled subscription.
     * 
     * @return Collection<int, SubscriptionContract>
     */
    protected function getCancelledSubscriptions(): Collection
    {
        return $this->cancelledSubscriptions ??
            $this->cancelledSubscriptions = collect();
    }

    /**
     * Getting subscribed subscription.
     * 
     * @return Collection<int, SubscriptionContract>
     */
    protected function getSubscribedSubscriptions(): Collection
    {
        return $this->subscribedSubscriptions ??
            $this->subscribedSubscriptions = collect();
    }
}