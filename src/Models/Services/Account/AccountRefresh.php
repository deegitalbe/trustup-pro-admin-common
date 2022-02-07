<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account\AccountRefreshContract;

/**
 * Account refresher service.
 */
class AccountRefresh implements AccountRefreshContract
{
    /**
     * Professional.
     * 
     * @var ProfessionalContract
     */
    protected $professional;

    /**
     * App.
     * 
     * @var AppContract
     */
    protected $app;

    /**
     * Refresh account common attributes.
     * 
     * @param AccountContract $account
     * @return AccountContract
     */
    public function refreshCommonAttributes(AccountContract $account): AccountContract
    {
        return $account->setSynchronizedAt(now())
            ->setApp($this->getApp())
            ->setProfessional($this->getProfessional())
            ->setInitialCreatedAt($this->getProfessional()->getCreatedAt());
    }

    /**
     * Setting professional.
     * 
     * @param ProfessionalContract $professional
     * @return AccountRefreshContract
     */
    public function setProfessional(ProfessionalContract $professional): AccountRefreshContract
    {
        $this->professional = $professional;

        return $this;
    }

    /**
     * Getting professional.
     * 
     * @return ProfessionalContract
     */
    public function getProfessional(): ProfessionalContract
    {
        return $this->professional;
    }

    /**
     * Setting linked app.
     * 
     * This options is here to avoid database hit based on app response.
     * 
     * @param AppContract $app
     * @return AccountRefreshContract
     */
    public function setApp(AppContract $app): AccountRefreshContract
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Getting linked app.
     * 
     * @return AppContract
     */
    public function getApp(): AppContract
    {
        return $this->app;
    }

}