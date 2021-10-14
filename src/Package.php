<?php
namespace Deegitalbe\TrustupProAdminCommon;

class Package
{
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
     * Getting package prefix.
     * 
     * @return string
     */
    public function prefix(): string
    {
        return "trustup-pro-admin-common";
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