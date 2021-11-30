<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account;

use stdClass;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AppQueryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account\AccountSetterByAppResponseContract;

/**
 * Setting account based on given app account response.
 */
class AccountSetterByAppResponse implements AccountSetterByAppResponseContract
{
     
    /**
     * Professional linked to response.
     * 
     * @var ProfessionalContract|null
     */
    protected $professional;

    /**
     * App linked to response.
     * 
     * @var AppContract|null
     */
    protected $app;

    /**
     * Setting professional (optional).
     * 
     * This options is here to avoid database hit based on app response.
     * 
     * @param ProfessionalContract $professional
     * @return AccountSetterByAppResponseContract
     */
    public function setProfessional(ProfessionalContract $professional): AccountSetterByAppResponseContract
    {
        $this->professional = $professional;

        return $this;
    }

    /**
     * Setting app (optional).
     * 
     * This options is here to avoid database hit based on app response.
     * 
     * @param AppContract $app
     * @return AccountSetterByAppResponseContract
     */
    public function setApp(AppContract $app): AccountSetterByAppResponseContract
    {
        $this->app = $app;

        return $this;
    }

    /**
     * Creating or updating account based on app response.
     * 
     * @param stdClass $app_account
     * @return AccountContract
     */
    public function getAccount(stdClass $app_account): AccountContract
    {
        $professional = $this->getProfessional($app_account);
        $app = $this->getApp($app_account);

        // Retrieve model or create it.
        $query = Package::account()::withTrashed()
            ->whereProfessional($professional)
            ->whereApp($app)
            ->where(function($query) use ($app_account) {
                // Having same uuid or not having uuid at all.
                return $query->whereUuid($app_account->uuid)
                    ->orWhereNull('uuid');
            });

        $model = $query->first() ?? app()->make(AccountContract::class);
        $model
            ->setDeletedAt(null)
            ->setSynchronizedAt(now())
            ->setUuid($app_account->uuid)
            ->setProfessional($professional)
            ->setApp($app)
            ->setRaw((array) $app_account)
            ->setInitialCreatedAt($app_account->created_at);
        
        // every account that is not concerning a paid application should have active status
        if (!$app->getPaid()):
            return $model->setChargebee(
                app()->make(AccountChargebeeContract::class)
                    ->setStatus('active')
            );
        endif;


        $status = $app_account->chargebee_subscription_status ?? null;
        $subscriptionId = $app_account->chargebee_subscription_id ?? null;
        if ( $status && $subscriptionId ) {
            $model->setChargebee(
                app()->make(AccountChargebeeContract::class)
                    ->setStatus($status)
                    ->setId($subscriptionId)
            );
        }

        return $model;
    }

    /**
     * Getting app linked to response.
     * 
     * @param stdClass $app_account
     * @return AppContract
     */
    protected function getApp(stdClass $app_account): AppContract
    {
        return $this->app ?? app()->make(AppQueryContract::class)->whereKeyIs($app_account->app_key)->first();
    }

    /**
     * Getting professional linked to response.
     * 
     * @param stdClass $app_account
     * @return ProfessionalContract
     */
    protected function getProfessional(stdClass $app_account): ProfessionalContract
    {
        return $this->professional ?? Package::professional()::where('authorization_key', $app_account->authorization_key)->first();
    }
}