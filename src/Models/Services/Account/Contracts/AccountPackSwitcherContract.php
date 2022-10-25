<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;

interface AccountPackSwitcherContract
{
    /**
     * Moving given professional accounts to grouped subscription pack.
     * 
     * @param ProfessionalContract $professional
     * @return bool Success state
     */
    public function toPack(ProfessionalContract $professional): bool;
}