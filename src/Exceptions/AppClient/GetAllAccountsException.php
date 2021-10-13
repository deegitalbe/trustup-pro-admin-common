<?php
namespace Deegitalbe\TrustupProAdminCommon\Exceptions\AppClient;

use Henrotaym\LaravelApiClient\Exceptions\RequestRelatedException;

class GetAllAccountsException extends RequestRelatedException
{
    /**
     * Exception message.
     * 
     * @var string
     */
    protected $message = "Request fetching all app accounts failed.";
}