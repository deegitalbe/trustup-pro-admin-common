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
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AppQueryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectClientContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupVersionedPackage\Contracts\VersionedPackageCheckerContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryUserContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountSetterByAppResponse;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\AutoRegisterContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account\AccountSetterByAppResponseContract;

class TrustupProAdminCommonServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->bindFacade()
            ->registerConfig()
            ->bindModels()
            ->bindServices();
            // ->bindProjects();
        
        $this->app->bind(AppClientContract::class, AppClient::class);
    }

    public function boot()
    {
        $this->makeConfigPublishable()
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

    protected function registerConfig(): self
    {
        $this->mergeConfigFrom($this->getConfigPath(), Package::prefix());

        return $this;
    }

    protected function registerPackageCommands(): self
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallPackage::class
            ]);
        }

        return $this;
    }

    protected function bindFacade(): self
    {
        $this->app->bind(UnderlyingPackage::$prefix, function($app) {
            return new UnderlyingPackage();
        });

        return $this;
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

        return $this;
    }

    protected function makeConfigPublishable(): self
    {
        if ($this->app->runningInConsole()):
            $this->publishes([
              $this->getConfigPath() => config_path(Package::prefix() . '.php'),
            ], 'config');
        endif;

        return $this;
    }

    protected function getConfigPath(): string
    {
        return __DIR__.'/../config/config.php';
    }
}