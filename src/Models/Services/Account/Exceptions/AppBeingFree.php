<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions;

use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\Abstracts\AccountSubscriberExceptionRelated;

class AppBeingFree extends AccountSubscriberExceptionRelated
{
    protected $message = "[Account subscription] Related app is free.";

    protected $code = 422;
}