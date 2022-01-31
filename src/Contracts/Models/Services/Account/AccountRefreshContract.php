<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;

/**
 * Account refresher service.
 */
interface AccountRefreshContract
{
    /**
     * Setting linked app.
     * 
     * @param AppContract $app
     * @return AccountRefreshContract
     */
    public function setApp(AppContract $app): AccountRefreshContract;

    /**
     * Setting linked professional.
     * 
     * @param ProfessionalContract $professional
     * @return AccountRefreshContract
     */
    public function setProfessional(ProfessionalContract $professional): AccountRefreshContract;

    /**
     * Refresh account common attributes.
     * 
     * @param AccountContract $account
     * @return AccountContract
     */
    public function refreshCommonAttributes(AccountContract $account): AccountContract;
}