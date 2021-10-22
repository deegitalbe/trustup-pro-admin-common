<?php
namespace Deegitalbe\TrustupProAdminCommon;

use Illuminate\Support\Collection;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectContract;

class Package
{
    /**
     * Getting package version (useful to make sure projetcs use same version).
     * 
     * @return string
     */
    public function version(): string
    {
        return "2.3.0";
    }

    /**
     * Getting package prefix.
     * 
     * @return string
     */
    public function prefix(): string
    {
        return "trustup-pro-admin-common";
    }

    /**
     * Getting projects linked to this package.
     */
    public function projects(): Collection
    {
        return collect($this->config('projects'))
            ->filter()
            ->map(function($url) { return app()->make(ProjectContract::class)->setUrl($url); });
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
     * Getting professional class name.
     * 
     * @return string
     */
    public function professional(): string
    {
        return $this->config('models.professional');
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
        return config($this->prefix(). ($key ? ".$key" : ''));
    }

    /**
     * Getting server authorization allowing to make requests to applications.
     * 
     * @return string
     */
    public function authorization(): string
    {
        return $this->config('authorization') ?? "";
    }
}