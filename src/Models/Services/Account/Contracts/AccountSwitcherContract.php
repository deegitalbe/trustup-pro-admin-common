<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\ContextualContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;

/**
 * Switching account owner.
 */
interface AccountSwitcherContract extends ContextualContract
{
    /**
     * Switching account to given professional.
     * 
     * Professional needs to have a chargebee_customer_id in order to work.
     * 
     * @param AccountContract $account
     * @param ProfessionalContract $professional
     * @return bool
     */
    public function switchToProfessional(AccountContract $account, ProfessionalContract $professional): bool;

    /**
     * Switching account to related professional main customer.
     * 
     * Professional needs to have a chargebee_customer_id in order to work.
     * 
     * @param AccountContract $account
     * @return bool
     */
    public function switchToProfessionalCustomer(AccountContract $account): bool;

    /**
     * Switching account to given customer.
     * 
     * @param AccountContract $account
     * @param CustomerContract $customer
     * @return bool
     */
    public function switchToCustomer(AccountContract $account, CustomerContract $customer): bool;

    /**
     * Getting account.
     * 
     * @return AccountContract
     */
    public function getAccount(): AccountContract;

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
     * Getting subscription.
     * 
     * @return SubscriptionPlanContract|null
     */
    public function getSubscriptionPlan(): ?SubscriptionPlanContract;
}