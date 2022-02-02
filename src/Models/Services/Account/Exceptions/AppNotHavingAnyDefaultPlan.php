<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions;

use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\Abstracts\AccountSubscriberExceptionRelated;

class AppNotHavingAnyDefaultPlan extends AccountSubscriberExceptionRelated
{
    protected $message = "[Account subscription] Related app doesn't have any default plan to use.";

    protected $code = 422;
}