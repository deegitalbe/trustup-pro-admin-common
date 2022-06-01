<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts;

use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\ContextualContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\UserContract;

interface AccountSubscriberContract extends ContextualContract
{
    /**
     * Subscribing given account.
     * 
     * @param AccountContract $account
     * @param UserContract $user Used as callback for customer if professional not being one already.
     * @return AccountContract
     */
    public function subscribeToDefaultPlan(AccountContract $account, UserContract $user): bool;

    /**
     * Subscribing given account.
     * 
     * @param AccountContract $account
     * @param PlanContract $plan
     * @param UserContract $user Used as callback for customer if professional not being one already.
     * @return AccountContract
     */
    public function subscribeToPlan(AccountContract $account, PlanContract $plan, UserContract $user): bool;

    /**
     * Subscribing given account with given customer.
     * 
     * @param AccountContract $account
     * @param PlanContract $plan
     * @param CustomerContract $customer.
     * @return AccountContract
     */
    public function subscribeToPlanWithCustomer(AccountContract $account, PlanContract $plan, CustomerContract $customer): bool;

    /**
     * Getting account.
     * 
     * @return AccountContract
     */
    public function getAccount(): AccountContract;

    /**
     * Getting user.
     * 
     * @return UserContract
     */
    public function getUser(): UserContract;

    /**
     * Getting plan.
     * 
     * @return PlanContract|null
     */
    public function getPlan(): ?PlanContract;

    /**
     * Getting customer.
     * 
     * @return CustomerContract|null
     */
    public function getCustomer(): ?CustomerContract;

    /**
     * Getting subscription.
     * 
     * @return SubscriptionContract|null
     */
    public function getSubscription(): ?SubscriptionContract;

    /**
     * Getting subscription plan.
     * 
     * @return SubscriptionPlanContract|null
     */
    public function getSubscriptionPlan(): ?SubscriptionPlanContract;
}