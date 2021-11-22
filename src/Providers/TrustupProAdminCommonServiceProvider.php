<?php
namespace Deegitalbe\TrustupProAdminCommon\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Deegitalbe\TrustupProAdminCommon\App\AppClient;
use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Project\Project;
use Deegitalbe\TrustupProAdminCommon\Models\Query\AppQuery;
use Deegitalbe\TrustupProAdminCommon\Project\ProjectClient;
use Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntry;
use Deegitalbe\TrustupProAdminCommon\Commands\CheckPackageVersion;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Package as UnderlyingPackage;
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

class TrustupProAdminCommonServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerConfig()
            ->bindFacade()
            ->bindModels();
            // ->bindProjects();
        
        $this->app->bind(AppClientContract::class, AppClient::class);
        $this->app->bind(AppQueryContract::class, AppQuery::class);
    }

    public function boot()
    {
        $this->makeConfigPublishable()
            // ->registerPackageCommands()
            // ->loadRoutes();
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
        $this->mergeConfigFrom($this->getConfigPath(), 'trustup-pro-admin-common');

        return $this;
    }

    // protected function registerPackageCommands(): self
    // {
    //     if ($this->app->runningInConsole()) {
    //         $this->commands([
    //             //
    //         ]);
    //     }

    //     return $this;
    // }

    protected function bindFacade(): self
    {
        $this->app->bind('trustup_pro_admin_common', function($app) {
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
        $this->app->bind(AccountChargebeeContract::class, Package::accountChargebee());

        return $this;
    }

    protected function makeConfigPublishable(): self
    {
        if ($this->app->runningInConsole()):
            $this->publishes([
              $this->getConfigPath() => config_path('trustup-pro-admin-common.php'),
            ], 'config');
        endif;

        return $this;
    }

    protected function getConfigPath(): string
    {
        return __DIR__.'/../config/config.php';
    }
}