<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions;

use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\Abstracts\AccountSubscriberExceptionRelated;

class PlanNotBelongingToApp extends AccountSubscriberExceptionRelated
{
    protected $message = "[Account subscription] Plan do not belong to related app.";

    protected $code = 422;
}