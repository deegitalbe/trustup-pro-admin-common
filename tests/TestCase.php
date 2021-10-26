<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests;

use Henrotaym\LaravelTestSuite\TestSuite;
use Jenssegers\Mongodb\MongodbServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Deegitalbe\TrustupProAdminCommon\Tests\MongoTestDatabase;
use Henrotaym\LaravelHelpers\Providers\HelperServiceProvider;
use Henrotaym\LaravelApiClient\Providers\ClientServiceProvider;
use Deegitalbe\TrustupProAdminCommon\Providers\TrustupProAdminCommonServiceProvider;
use Deegitalbe\TrustupVersionedPackage\Providers\TrustupVersionedPackageServiceProvider;

class TestCase extends BaseTestCase
{
    use
        MongoTestDatabase,
        TestSuite
    ;
    
    /**
     * Providers used bu application (manual registration is compulsory)
     * 
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            TrustupVersionedPackageServiceProvider::class,
            TrustupProAdminCommonServiceProvider::class,
            MongodbServiceProvider::class,
            ClientServiceProvider::class,
            HelperServiceProvider::class
        ];
    }
}