<?php
namespace Deegitalbe\TrustupProAdminCommon\Exceptions\AppClient;

use Deegitalbe\TrustupProAdminCommon\Exceptions\AppClient\GetAccountsException;

class UpdateAccountException extends GetAccountsException
{
    /**
     * Exception message.
     * 
     * @var string
     */
    protected $message = "Request updating account failed.";
}