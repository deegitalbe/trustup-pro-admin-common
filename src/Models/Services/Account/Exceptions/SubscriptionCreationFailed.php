<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions;

use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\Abstracts\AccountSubscriberExceptionRelated;

class SubscriptionCreationFailed extends AccountSubscriberExceptionRelated
{
    protected $message = "[Account subscription] Error during API request to chargebee. Unable to create subscription.";

    protected $code = 500;
}