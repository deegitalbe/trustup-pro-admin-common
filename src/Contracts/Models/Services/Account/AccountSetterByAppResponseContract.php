<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account;

use stdClass;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;

/**
 * Setting account based on given app account response.
 */
interface AccountSetterByAppResponseContract
{
    /**
     * Setting professional (optional).
     * 
     * This options is here to avoid database hit based on app response.
     * 
     * @param ProfessionalContract $professional
     * @return AccountSetterByAppResponseContract
     */
    public function setProfessional(ProfessionalContract $account): AccountSetterByAppResponseContract;

    /**
     * Setting app (optional).
     * 
     * This options is here to avoid database hit based on app response.
     * 
     * @param AppContract $app
     * @return AccountSetterByAppResponseContract
     */
    public function setApp(AppContract $app): AccountSetterByAppResponseContract;

    /**
     * Creating or updating account based on app response.
     * 
     * @param stdClass $app_account
     * @return AccountContract
     */
    public function getAccount(stdClass $app_account): AccountContract;
}