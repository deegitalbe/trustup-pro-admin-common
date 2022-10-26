<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account;

use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AccountQueryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\PlanQueryContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountDedicatedSubscriptionSwitcherContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountSubscriberContract;
use Illuminate\Support\Collection;

class AccountDedicatedSubscriptionSwitcher implements AccountDedicatedSubscriptionSwitcherContract
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
     * Sucessfully subscribed subscriptions.
     * 
     * @var Collection<int, SubscriptionContract>
     */
    protected Collection $subscribedSubscriptions;

    /**
     * Pack subscription to cancel.
     * 
     * @var SubscriptionContract
     */
    protected ?SubscriptionContract $packSubscription;

    /**
     * Pack subscription status (before cancellation).
     * 
     * @var AccountChargebeeContract
     */
    protected AccountChargebeeContract $packSubscriptionStatus;

    /**
     * Injecting dependencies.
     * 
     * @param SubscriptionApiContract $subscriptionApi
     * @param AccountSubscriberContract $subscriber
     * @return void
     */
    public function __construct(
        SubscriptionApiContract $subscriptionApi,
        AccountSubscriberContract $subscriber
    ) {
        $this->subscriptionApi = $subscriptionApi;
        $this->subscriber = $subscriber;
    }

    /**
     * Moving given professional accounts from pack to dedicated subscriptions.
     * 
     * @param ProfessionalContract $professional
     * @return bool Success state
     */
    public function toDedicatedSubscriptions(ProfessionalContract $professional): bool
    {
        $this->setProfessional($professional)
            ->setPackSubscription($professional->getPackSubscription())
            ->setAccounts($this->getAccountsToSubscribe());

        if (!$this->hasPackSubscription()) return false;
        if (!$this->cancelPackSubscription()->hasPackSubscription()) return false;

        return $this->updateProfessionalPackSubscription()
            ->subscribeAccounts()
            ->hasSuccessfullySubscribedAccounts();
    }

    /**
     * Cancelling pack subscription.
     * 
     * @return static
     */
    protected function cancelPackSubscription(): self
    {
        $this->setPackSubscriptionStatus();

        if ($this->packSubscriptionStatus->isCancelled()) return $this;

        if (!$this->packSubscriptionStatus->cancellable()) return $this->setPackSubscription(null);

        return $this->setPackSubscription(
            $this->subscriptionApi->cancelNow($this->packSubscription, true)
        );
    }

    /**
     * Getting accounts that should be subscribed to dedicated subscriptions.
     * 
     * @return Collection<int, AccountContract>
     */
    protected function getAccountsToSubscribe(): ?Collection
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
     * Getting account subscription dedicated plan.
     * 
     * @param AccountContract $account
     * @return ?PlanContract 
     */
    protected function getAccountPlan(AccountContract $account): ?PlanContract
    {
        /** @var PlanQueryContract */
        $query = app()->make(PlanQueryContract::class);

        $account->getChargebee()->getPlan()->isYearlyBilled() ?
            $query->beingYearlyDefault()
            : $query->beingDefault();

        return $query->whereApp($account->getApp())->first();
    }

    /**
     * Updating professional pack subscription.
     * 
     * @return static
     */
    protected function updateProfessionalPackSubscription(): self
    {
        $this->professional->chargebee_subscription_pro_pack_id = null;
        $this->professional->persist();

        return $this;
    }

    /**
     * Subscribe professional accounts to dedicated subscriptions.
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
     * Subscribe given account to dedicated subscription.
     * 
     * @param AccountContract $account
     * @return void
     */
    protected function subscribeAccount(AccountContract $account): void
    {
        // Try to create dedicated subscription for account.
        $this->subscriber->subscribeToPlanWithCustomer(
            $account,
            $this->getAccountPlan($account),
            $this->professional->getCustomer()
        );

        // Stop if subscription was not created.
        if (!$this->subscriber->getSubscription()) return;

        $subscription = $this->subscriber->getSubscription();

        // If pack was in trial stop and consider subscription as successfull.
        if ($this->packSubscriptionStatus->isTrial()):
            $this->successfullySubscribedAccount($account, $subscription);
            return;
        endif;

        // Stop if activation failed.
        // @TODO Uncomment this line to handle activation process.
        // if (!$subscription = $this->activateSubscription($this->subscriber->getSubscription())) return;

        // Consider subscription as successfull.
        $this->successfullySubscribedAccount($account, $subscription);
    }

    /**
     * Trying to activate given subscription.
     * 
     * @TODO If activation fails, set it back to trial.
     * 
     * @return ?SubscriptionContract
     */
    protected function activateSubscription(SubscriptionContract $subscription): ?SubscriptionContract
    {
        // Stop if subscription cancellation failed.
        if (!$subscription = $this->subscriptionApi->cancelNow($subscription)) return null;
        
        // Stop if subscription reactivation failed.
        if (!$subscription = $this->subscriptionApi->reactivate($subscription)):
            // @TODO if reactivation failed, set it back to trial.
            return null;
        endif;

        return $subscription;
    }

    /**
     * Refreshing account status based on given subscription.
     * 
     * @param AccountContract $account
     * @param SubscriptionContract $subscription
     * @return void
     */
    protected function successfullySubscribedAccount(AccountContract $account, SubscriptionContract $subscription): void
    {
        // Refresh account status based on subscription.
        $account->getChargebee()->refreshFromSubscription($subscription);

        // Push subscription as successfull.
        $this->getSubscribedSubscriptions()->push($subscription);
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
     * Telling if related pack subscription was set successfully.
     * 
     * @return bool
     */
    protected function hasPackSubscription(): bool
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
     * Setting pack subscription status (before cancellation).
     * 
     * @return static
     */
    protected function setPackSubscriptionStatus(): self
    {
        /** @var AccountChargebeeContract */
        $status = app()->make(AccountChargebeeContract::class);
        $status->setStatus($this->packSubscription->getStatus());
        
        $this->packSubscriptionStatus = $status;

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