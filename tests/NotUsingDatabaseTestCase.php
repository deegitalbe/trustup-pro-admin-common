<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests;

use Deegitalbe\TrustupProAdminCommon\Package;
use Jenssegers\Mongodb\MongodbServiceProvider;
use Henrotaym\LaravelApiClient\Providers\ClientServiceProvider;
use Deegitalbe\ChargebeeClient\Providers\ChargebeeClientProvider;
use Henrotaym\LaravelPackageVersioning\Testing\VersionablePackageTestCase;
use Henrotaym\LaravelModelQueries\Providers\LaravelModelQueriesServiceProvider;
use Deegitalbe\ServerAuthorization\Providers\ServerAuthorizationServiceProvider;
use Deegitalbe\TrustupProAdminCommon\Providers\TrustupProAdminCommonServiceProvider;
use Deegitalbe\TrustupVersionedPackage\Providers\TrustupVersionedPackageServiceProvider;

class NotUsingDatabaseTestCase extends VersionablePackageTestCase
{
    public static function getPackageClass(): string
    {
        return Package::class;
    }

    /**
     * Getting service providers to add to default ones.
     * 
     * @return array
     */
    public function getServiceProviders(): array
    {
        return [
            TrustupVersionedPackageServiceProvider::class,
            ServerAuthorizationServiceProvider::class,
            TrustupProAdminCommonServiceProvider::class,
            MongodbServiceProvider::class,
            ClientServiceProvider::class,
            ChargebeeClientProvider::class,
            LaravelModelQueriesServiceProvider::class
        ];
    }
}