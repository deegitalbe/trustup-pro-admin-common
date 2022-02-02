<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\UserContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\AppBeingFree;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\NotFindingCustomer;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\PlanNotBelongingToApp;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountSubscriberContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\AccountNotLinkedToAnyApp;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\AppNotHavingAnyDefaultPlan;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\SubscriptionCreationFailed;

class AccountSubscriber implements AccountSubscriberContract
{
    /** @var AccountContract */
    protected $account;

    /** @var UserContract */
    protected $user;

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

    public function __construct(SubscriptionApiContract $subscription_api)
    {
        $this->subscription_api = $subscription_api;
    }

    /**
     * Subscribing given account.
     * 
     * @param AccountContract $account
     * @param UserContract $user Used as callback for customer if professional not being one already.
     * @return AccountContract
     */
    public function subscribeToDefaultPlan(AccountContract $account, UserContract $user): bool
    {
        $this->fresh()
            ->setAccount($account)
            ->setUser($user);

        if (!$plan = optional($account->getApp())->getDefaultPlan()):
            return $this->appNotHavingAnyDefaultPlan();
        endif;

        return $this->subscribeToPlan($account, $plan, $user);
    }

    /**
     * Subscribing given account.
     * 
     * @param AccountContract $account
     * @param PlanContract $plan
     * @param UserContract $user Used as callback for customer if professional not being one already.
     * @return AccountContract
     */
    public function subscribeToPlan(AccountContract $account, PlanContract $plan, UserContract $user): bool
    {
        $this->fresh()
            ->setUser($user)
            ->setAccount($account)
            ->setPlan($plan);

        if (!$this->successfullySubscribedAccount()):
            return false;
        endif;

        $this->updateAccountStatus()
            ->updateProfessionalCustomer();
        
        return true;
    }

    /**
     * Trying to do account subscribing process.
     * 
     * @return bool Success state.
     */
    protected function successfullySubscribedAccount(): bool
    {
        return $this->isRelatedAppPaid()
            && $this->isAccountCompatibleWithPlan()
            && $this->foundACustomer()
            && $this->createdASubscription();
    }

    /**
     * Telling if related app is eligible to subscribe.
     * 
     * @return bool
     */
    protected function isRelatedAppPaid(): bool
    {
        if (!$app = $this->account->getApp()):
            return $this->accountNotLinkedToAnyApp();
        endif;

        if (!$app->getPaid()):
            return $this->appBeingFree();
        endif;

        return true;
    }

    /**
     * Telling if account is compatible with plan.
     * 
     * @return bool
     */
    protected function isAccountCompatibleWithPlan(): bool
    {
        if($this->account->getApp()->getKey() !== $this->plan->getApp()->getKey()):
            return $this->planNotBelongingToApp();
        endif;

        return true;
    }

    /**
     * Setting customer to link to based on account or user (as fallback).
     * 
     * @return CustomerContract|null
     */
    protected function getCustomerFromAccountOrUser(): ?CustomerContract
    {
        $customer = $this->account->getProfessional()->getCustomer();

        // If not found => from user
        if (!$customer):
            return $this->user->toCustomer();
        endif;

        return $customer;
    }

    /**
     * Trying to find a related customer.
     * 
     * @return bool
     */
    protected function foundACustomer(): bool
    {
        $this->setCustomerFromAccountOrUser();

        if (!$this->customer):
            return $this->notFindingCustomer();
        endif;

        return true;
    }

    /**
     * Setting customer to link to based on account or user (as fallback).
     * 
     * @return self
     */
    protected function setCustomerFromAccountOrUser(): self
    {
        return $this->setCustomer($this->getCustomerFromAccountOrUser());
    }

    /**
     * Trying to create subscription.
     * 
     * @return bool
     */
    protected function createdASubscription(): bool
    {
        $this->createSubscription();

        if(!$this->subscription):
            return $this->subscriptionCreationFailed();
        endif;

        return true;
    }

    /**
     * Creating subscription.
     * 
     * @return self
     */
    protected function createSubscription(): self
    {
        $this->setSubscription($this->subscription_api->create($this->subscription_plan, $this->customer));

        if(!$this->subscription):
            return $this;
        endif;

        return $this->setCustomer($this->subscription->getCustomer());
    }

    /**
     * Updating professional related customer.
     * 
     * @return self
     */
    protected function updateProfessionalCustomer(): self
    {
        $this->account->getProfessional()
            ->setCustomer($this->customer)
            ->persist();

        return $this;
    }

    /**
     * Updating professional related customer.
     * 
     * @return self
     */
    protected function updateAccountStatus(): self
    {
        $status = app()->make(AccountChargebeeContract::class)
            ->fromSubscription($this->subscription)
            ->persist();

        $this->account->setChargebee($status);

        return $this;
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
     * Setting account.
     * 
     * @param UserContract $user
     * @return self
     */
    protected function setUser(UserContract $user): self
    {
        $this->user = $user;

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
        
        return $this->setSubscriptionPlan($plan->toSubscriptionPlan());
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
     * Getting user.
     * 
     * @return UserContract
     */
    public function getUser(): UserContract
    {
        return $this->user;
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

    /**
     * Happening when account is not linked to any app.
     * 
     * @return bool
     */
    protected function accountNotLinkedToAnyApp(): bool
    {
        report(AccountNotLinkedToAnyApp::create($this));

        return false;
    }

    /**
     * Happening when app linked to account is free.
     * 
     * @return bool
     */
    protected function appBeingFree(): bool
    {
        report(AppBeingFree::create($this));

        return false;
    }

    /**
     * Happening when app linked to account is not having any default plan.
     * 
     * @return bool
     */
    protected function appNotHavingAnyDefaultPlan(): bool
    {
        report(AppNotHavingAnyDefaultPlan::create($this));

        return false;
    }

    /**
     * Happening when app linked to account is not having any default plan.
     * 
     * @return bool
     */
    protected function subscriptionCreationFailed(): bool
    {
        report(SubscriptionCreationFailed::create($this));

        return false;
    }

    /**
     * Happening when we can't create/retrieve a customer from professional or given user.
     * 
     * @return bool
     */
    protected function notFindingCustomer(): bool
    {
        report(NotFindingCustomer::create($this));

        return false;
    }

    /**
     * Happening when we try to subscribe a plan that is not linked to account related app.
     * 
     * @return bool
     */
    protected function planNotBelongingToApp(): bool
    {
        report(PlanNotBelongingToApp::create($this));

        return false;
    }

    public function context(): array
    {
        return [
            'account' => $this->account,
            'professional' => $this->account->getProfessional(),
            'app' => $this->account->getApp(),
            'plan' => $this->plan,
            'user' => $this->user,
            'customer' => $this->customer,
            'subscription' => $this->subscription,
            'subscription_plan' => $this->subscription_plan,
        ];
    }
}