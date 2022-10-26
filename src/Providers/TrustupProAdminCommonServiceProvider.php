<?php
namespace Deegitalbe\TrustupProAdminCommon\Providers;

use Deegitalbe\TrustupProAdminCommon\App\AppClient;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Models\Query\AppQuery;
use Deegitalbe\TrustupProAdminCommon\Commands\InstallPackage;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Package as UnderlyingPackage;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\App\AppClientContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountRefresh;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountSwitcher;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountSubscriber;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupVersionedPackage\Contracts\VersionedPackageCheckerContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryUserContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountSetterByAppResponse;
use Henrotaym\LaravelPackageVersioning\Providers\Abstracts\VersionablePackageServiceProvider;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account\AccountRefreshContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountSwitcherContract;
use Henrotaym\LaravelContainerAutoRegister\Services\AutoRegister\Contracts\AutoRegisterContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountSubscriberContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account\AccountSetterByAppResponseContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountDedicatedSubscriptionSwitcher;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountPackSwitcher;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountDedicatedSubscriptionSwitcherContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountPackSwitcherContract;
use PDO;

class TrustupProAdminCommonServiceProvider extends VersionablePackageServiceProvider
{
    public static function getPackageClass(): string
    {
        return UnderlyingPackage::class;
    }

    protected function addToRegister(): void
    {
        $this
            ->addAdminConnection()
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
        $this->app->bind(AccountSubscriberContract::class, AccountSubscriber::class);
        $this->app->bind(AccountSwitcherContract::class, AccountSwitcher::class);
        $this->app->bind(AccountPackSwitcherContract::class, AccountPackSwitcher::class);
        $this->app->bind(AccountDedicatedSubscriptionSwitcherContract::class, AccountDedicatedSubscriptionSwitcher::class);

        return $this;
    }

    /**
     * Adding admin connection used by package.
     * 
     * @return self
     */
    protected function addAdminConnection(): self
    {
        config([
            'database.connections.'. Package::adminConnection() => [
                'driver' => 'mysql',
                'url' => env('DATABASE_ADMIN_URL'),
                'host' => env('DB_ADMIN_HOST', '127.0.0.1'),
                'port' => env('DB_ADMIN_PORT', '3306'),
                'database' => env('DB_ADMIN_DATABASE', 'forge'),
                'username' => env('DB_ADMIN_USERNAME', 'forge'),
                'password' => env('DB_ADMIN_PASSWORD', ''),
                'unix_socket' => env('DB_ADMIN_SOCKET', ''),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'prefix_indexes' => true,
                'strict' => true,
                'engine' => null,
                'options' => extension_loaded('pdo_mysql') ? array_filter([
                    PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ]) : [],
            ]
        ]);

        return $this;
    }
}