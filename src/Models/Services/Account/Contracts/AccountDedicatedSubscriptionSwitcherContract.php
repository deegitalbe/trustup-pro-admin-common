<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;

interface AccountDedicatedSubscriptionSwitcherContract
{
    /**
     * Moving given professional accounts from pack to dedicated subscriptions.
     * 
     * @param ProfessionalContract $professional
     * @return bool Success state
     */
    public function toDedicatedSubscriptions(ProfessionalContract $professional): bool;
}