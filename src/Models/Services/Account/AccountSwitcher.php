<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\UserContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountSubscriberContract;

/**
 * Switching account owner.
 */
class AccountSwitcher implements AccountSwitcherContract
{
    /** @var AccountContract */
    protected $account;

    /** @var PlanContract|null */
    protected $plan;

    /** @var CustomerContract|null */
    protected $customer;

    /** @var SubscriptionPlanContract|null */
    protected $subscription_plan;

    /** @var SubscriptionContract|null */
    protected $subscription;

    /** @var SubscriptionApiContract */
    protected $subscription_api;

    /** @var AccountSubscriberContract */
    protected $account_subscriber;

    public function __construct(
        SubscriptionApiContract $subscription_api,
        AccountSubscriberContract $account_subscriber,
    ) {
        $this->subscription_api = $subscription_api;
        $this->account_subscriber = $account_subscriber;
    }

    /**
     * Switching account to given professional.
     * 
     * Professional needs to have a chargebee_customer_id in order to work.
     * 
     * @param AccountContract $account
     * @param ProfessionalContract $professional
     * @return bool
     */
    public function switchToProfessional(AccountContract $account, ProfessionalContract $professional): bool
    {
        if (!$customer = $professional->getCustomer()):
            return false;
        endif;

        $account->setProfessional($professional)->persist()->updateInApp();

        return $this->switchToCustomer($account, $customer);
    }

    /**
     * Switching account to professional main customer.
     * 
     * Professional needs to have a chargebee_customer_id in order to work.
     * 
     * @param AccountContract $account
     * @return bool
     */
    public function switchToProfessionalCustomer(AccountContract $account): bool
    {
        if (!$customer = optional($account->getProfessional())->getCustomer()):
            return false;
        endif;

        return $this->switchToCustomer($account, $customer);
    }

    /**
     * Switching account to given customer.
     * 
     * @param AccountContract $account
     * @param CustomerContract $customer
     * @return bool
     */
    public function switchToCustomer(AccountContract $account, CustomerContract $customer): bool
    {
        $this->fresh()
            ->setCustomer($customer)
            ->setAccount($account)
            ->setPlan($account->getChargebee()->getPlan());

        if (!$this->successfullySwitchedAccount()):
            return false;
        endif;

        $this->updateAccountStatus();
    }

    /**
     * Telling if switch was successfull.
     * 
     * @return bool
     */
    protected function successfullySwitchedAccount(): bool
    {
        return $this->cancelExistingSubscription()
            && $this->createNewSubscription()
            && $this->activateNewSubscription();
    }

    /**
     * Cancelling existing subscription.
     * 
     * @return bool
     */
    protected function cancelExistingSubscription(): bool
    {
        if ($this->account->getChargebee()->isCancelled()):
            return true;
        endif;

        if (!$subscription = $this->subscription_api->find($this->account->getChargebee()->getId())):
            return true;
        endif;

        return !!$this->subscription_api->cancelNow($subscription);
    }

    /**
     * Creating new subscription.
     * 
     * @return bool
     */
    protected function createNewSubscription(): bool
    {
        $this->account_subscriber->subscribeToPlanWithCustomer($this->account, $this->plan, $this->customer);

        $this->setSubscription($this->account_subscriber->getSubscription());
        
        if (!$this->hasSubscription()):
            return false;
        endif;

        // Linking account to new subscription since old one is cancelled.
        return !!$this->account->getChargebee()->fromSubscription($this->subscription)->persist();
    }

    /**
     * Activating previously created subscription.
     * 
     * @return bool
     */
    protected function activateNewSubscription(): bool
    {
        $this->setSubscription($this->subscription_api->cancelNow($this->subscription));

        if (!$this->hasSubscription()):
            return null;
        endif;

        return $this->setSubscription($this->subscription_api->reactivate($this->subscription))->hasSubscription();
    }

    /**
     * Telling if class is having a related subscription.
     * 
     * @return bool
     */
    protected function hasSubscription(): bool
    {
        return !!$this->subscription;
    }

    /**
     * Updating account status in app environment.
     */
    protected function updateAccountStatus(): void
    {
        $this->account->getChargebee()->refreshFromSubscription($this->subscription, true);
    }

    /**
     * Making sure internal properties are reset and fresh.
     * 
     * @return self
     */
    protected function fresh(): self
    {
        return $this->setPlan(null)
            ->setCustomer(null)
            ->setSubscriptionPlan(null)
            ->setSubscription(null);
    }

    /**
     * Setting account.
     * 
     * @param AccountContract $account
     * @return self
     */
    protected function setAccount(AccountContract $account): self
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Setting plan.
     * 
     * @param PlanContract|null $plan
     * @return self
     */
    protected function setPlan(?PlanContract $plan): self
    {
        $this->plan = $plan;
        
        return $this->setSubscriptionPlan(optional($plan)->toSubscriptionPlan());
    }

    /**
     * Setting subscription plan.
     * 
     * @param SubscriptionPlanContract|null $subcription_plan
     * @return self
     */
    protected function setSubscriptionPlan(?SubscriptionPlanContract $subcription_plan): self
    {
        $this->subscription_plan = $subcription_plan;

        return $this;
    }

    /**
     * Setting customer.
     * 
     * @param CustomerContract|null $customer
     * @return self
     */
    protected function setCustomer(?CustomerContract $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Setting subscription.
     * 
     * @param SubscriptionContract|null $subscription
     * @return self
     */
    protected function setSubscription(?SubscriptionContract $subscription): self
    {
        $this->subscription = $subscription;

        return $this;
    }

    /**
     * Getting account.
     * 
     * @return AccountContract
     */
    public function getAccount(): AccountContract
    {
        return $this->account;
    }

    /**
     * Getting plan.
     * 
     * @return PlanContract|null
     */
    public function getPlan(): ?PlanContract
    {
        return $this->plan;
    }

    /**
     * Getting customer.
     * 
     * @return CustomerContract|null
     */
    public function getCustomer(): ?CustomerContract
    {
        return $this->customer;
    }

    /**
     * Getting subscription.
     * 
     * @return SubscriptionContract|null
     */
    public function getSubscription(): ?SubscriptionContract
    {
        return $this->subscription;
    }

    /**
     * Getting subscription.
     * 
     * @return SubscriptionPlanContract|null
     */
    public function getSubscriptionPlan(): ?SubscriptionPlanContract
    {
        return $this->subscription_plan;
    }

    public function context(): array
    {
        return [
            'account' => $this->account,
            'professional' => $this->account->getProfessional(),
            'app' => $this->account->getApp(),
            'plan' => $this->plan,
            'customer' => $this->customer,
            'subscription' => $this->subscription,
            'subscription_plan' => $this->subscription_plan,
        ];
    }
}