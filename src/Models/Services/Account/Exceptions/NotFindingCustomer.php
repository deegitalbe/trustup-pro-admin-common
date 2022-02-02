<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions;

use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\Abstracts\AccountSubscriberExceptionRelated;

class NotFindingCustomer extends AccountSubscriberExceptionRelated
{
    protected $message = "[Account subscription] Couldn't find/create a customer for subscription.";

    protected $code = 500;
}