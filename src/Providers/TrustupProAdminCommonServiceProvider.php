<?php
namespace Deegitalbe\TrustupProAdminCommon\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Deegitalbe\TrustupProAdminCommon\Models\Plan;
use Deegitalbe\TrustupProAdminCommon\App\AppClient;
use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Project\Project;
use Deegitalbe\TrustupProAdminCommon\Models\Query\AppQuery;
use Deegitalbe\TrustupProAdminCommon\Project\ProjectClient;
use Deegitalbe\TrustupProAdminCommon\Commands\InstallPackage;
use Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntry;
use Deegitalbe\TrustupProAdminCommon\Commands\CheckPackageVersion;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Package as UnderlyingPackage;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntryUser;
use Deegitalbe\TrustupProAdminCommon\Contracts\App\AppClientContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountRefresh;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AppQueryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectClientContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupVersionedPackage\Contracts\VersionedPackageCheckerContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryUserContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountSetterByAppResponse;
use Henrotaym\LaravelPackageVersioning\Providers\Abstracts\VersionablePackageServiceProvider;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account\AccountRefreshContract;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\AutoRegisterContract;
use Henrotaym\LaravelPackageVersioning\Services\Versioning\Contracts\VersioningRepositoryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account\AccountSetterByAppResponseContract;

class TrustupProAdminCommonServiceProvider extends VersionablePackageServiceProvider
{
    public static function getPackageClass(): string
    {
        return UnderlyingPackage::class;
    }

    protected function addToRegister(): void
    {
        $this
            ->bindModels()
            ->bindServices();

        $this->app->bind(AppClientContract::class, AppClient::class);
    }

    protected function addToBoot(): void
    {
        $this
            ->registerPackageCommands()
            // ->loadRoutes();
            ->bindQueries()
            ->registerPackage();
    }

    protected function registerPackage(): self
    {
        app()->make(VersionedPackageCheckerContract::class)
            ->addPackage(Package::getFacadeRoot());

        return $this;
    }

    // protected function loadRoutes(): self
    // {
    //     Route::group([
    //         //
    //     ], function () {
    //         $this->loadRoutesFrom(__DIR__.'/../routes/routes.php');
    //     });

    //     return $this;
    // }

    protected function registerPackageCommands(): self
    {
        return $this->registerCommand(InstallPackage::class);
    }

    protected function bindModels(): self
    {
        $this->app->bind(ProfessionalContract::class, Package::professional());
        $this->app->bind(AccountContract::class, Package::account());
        $this->app->bind(AccountAccessEntryContract::class, Package::accountAccessEntry());
        $this->app->bind(AccountAccessEntryUserContract::class, Package::accountAccessEntryUser());
        $this->app->bind(AppContract::class, Package::app());
        $this->app->bind(PlanContract::class, Package::plan());
        $this->app->bind(AccountChargebeeContract::class, Package::accountChargebee());

        return $this;
    }

    /**
     * Binding queries.
     * 
     * @return self
     */
    protected function bindQueries(): self
    {
        app()->make(AutoRegisterContract::class)
            ->scanWhere(AppQuery::class);

        return $this;
    }

    /**
     * Binding services.
     * 
     * @return self
     */
    protected function bindServices(): self
    {
        $this->app->bind(AccountSetterByAppResponseContract::class, AccountSetterByAppResponse::class);
        $this->app->bind(AccountRefreshContract::class, AccountRefresh::class);

        return $this;
    }
}