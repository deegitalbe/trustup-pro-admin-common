<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions;

use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\Abstracts\AccountSubscriberExceptionRelated;

class AccountNotLinkedToAnyApp extends AccountSubscriberExceptionRelated
{
    protected $message = "[Account subscription] Account missing related app.";

    protected $code = 422;
}