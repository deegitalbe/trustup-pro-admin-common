<?php
namespace Deegitalbe\TrustupProAdminCommon;

use Illuminate\Support\Collection;
use Deegitalbe\TrustupVersionedPackage\Contracts\Project\ProjectContract;
use Deegitalbe\TrustupVersionedPackage\Contracts\VersionedPackageContract;
use Henrotaym\LaravelPackageVersioning\Services\Versioning\VersionablePackage;

class Package extends VersionablePackage implements VersionedPackageContract
{
    /**
     * Package prefix.
     * 
     * @return string
     */
    public static function prefix(): string
    {
        return "trustup_pro_admin_common";
    }

    /**
     * Getting package version (useful to make sure projetcs use same version).
     * 
     * @return string
     */
    public function version(): string
    {
        return $this->getVersion();
    }

    /**
     * Getting account class name.
     * 
     * @return string
     */
    public function account(): string
    {
        return $this->config('models.account');
    }

    /**
     * Getting account access entry class name.
     * 
     * @return string
     */
    public function accountAccessEntry(): string
    {
        return $this->config('models.account_access_entry');
    }

    /**
     * Getting account access entry user class name.
     * 
     * @return string
     */
    public function accountAccessEntryUser(): string
    {
        return $this->config('models.account_access_entry_user');
    }

    /**
     * Getting account chargebee class name.
     * 
     * @return string
     */
    public function accountChargebee(): string
    {
        return $this->config('models.account_chargebee');
    }

    /**
     * Getting app class name.
     * 
     * @return string
     */
    public function app(): string
    {
        return $this->config('models.app');
    }

    /**
     * Getting plan model class name.
     * 
     * @return string
     */
    public function plan(): string
    {
        return $this->config('models.plan');
    }

    /**
     * Getting professional class name.
     * 
     * @return string
     */
    public function professional(): string
    {
        return $this->config('models.professional');
    }

    /**
     * Telling which connection should be used for admin database.
     * 
     * @return string
     */
    public function databaseConnection(): string
    {
        return $this->config('connection');
    }

    /**
     * Getting config value.
     * Prefix is automatically added to given key.
     * 
     * @param string $key key to get in config file.
     * @return mixed
     */
    public function config(string $key = null)
    {
        return $this->getConfig($key ?? "");
    }

    /**
     * Getting projects using this package.
     * 
     * @return Collection
     */
    public function getProjects(): Collection
    {
        return collect($this->config('projects'))
            ->filter()
            ->map(function($url) { 
                return app()->make(ProjectContract::class)
                    ->setUrl($url)
                    ->setVersionedPackage($this);
            });
    }

    /**
     *  Getting package name
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->getPrefix();
    }
}