<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests;

use Illuminate\Support\Facades\Schema;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Tests\Models\ProfessionalTestModel;
use Illuminate\Foundation\Testing\DatabaseMigrations as LaravelDatabaseMigrations;

trait MongoTestDatabase
{
    use LaravelDatabaseMigrations {
        runDatabaseMigrations as private parentRunDatabaseMigrations; 
    }

    public function runDatabaseMigrations()
    {
        $this->parentRunDatabaseMigrations();
        $this->setMongoTestDatabase()
            ->dropMongoCollections();
    }

    /**
     * Dropping mongo collections from database.
     * 
     * @return self
     */
    protected function dropMongoCollections(): self
    {
        // Linking fake professional model
        config([Package::getPrefix(). '.models.professional' => ProfessionalTestModel::class]);

        foreach($this->getMongoCollections() as $model => $class):
            // Avoid excluded models
            if (in_array($model, $this->getExcludedModels())):
                continue;
            endif;
            Schema::connection('mongodb')->dropIfExists($class::getModel()->getTable());
        endforeach;

        Schema::connection('mongodb')->dropIfExists(ProfessionalTestModel::getModel()->getTable());

        return $this;
    }

    /**
     * Excluded models from being refreshed.
     * 
     * @return array Should contains model keys defined in package config.
     */
    protected function getExcludedModels(): array
    {
        return [];
    }

    /**
     * Additional models being refreshed.
     * 
     * @return array Should contains model keys defined in package config.
     */
    protected function additionalModels(): array
    {
        return [];
    }

    /**
     * Getting all mongo collections that should be refreshed
     * 
     * @return array
     */
    protected function getMongoCollections(): array
    {
        return array_merge(Package::config('models'), $this->additionalModels());
    }

    /**
     * Defining a test mongo database to avoid any problem in production.
     * 
     * @return array Mongo connection.
     */
    protected function getMongoTestDatabase(): array
    {
        return [
            'driver' => 'mongodb',
            'dsn' => "mongodb+srv://root:root@trustup-pro-admin-commo.tifku.mongodb.net/test?authSource=admin&replicaSet=atlas-89qedz-shard-0&ssl=true",
            'host' => "trustup-pro-admin-commo.tifku.mongodb.net",
            'port' => "27017",
            'database' => "test",
            'username' => "root",
            'password' => "root",
            'options' => array_filter([
                'tlsCAFile' => false,
                'tls' => true,
                'database' => "admin",
                'tlsAllowInvalidCertificates' => true,
                'replicaSet' => "atlas-89qedz-shard-0",
            ])
        ];
    }

    /**
     * Overriding mongo connection in config at runtime.
     * By doing this models will use test database instead of production database.
     * 
     * @return self
     */
    protected function setMongoTestDatabase(): self
    {
        config(['database.connections.mongodb' => $this->getMongoTestDatabase()]);

        return $this;
    }
}

