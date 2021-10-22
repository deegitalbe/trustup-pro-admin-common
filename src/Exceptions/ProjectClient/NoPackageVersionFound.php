<?php
namespace Deegitalbe\TrustupProAdminCommon\Exceptions\ProjectClient;

use Henrotaym\LaravelApiClient\Exceptions\RequestRelatedException;

class NoPackageVersionFound extends RequestRelatedException
{
    /**
     * Exception message.
     * 
     * @var string
     */
    protected $message = "Request fetching package version failed.";
}