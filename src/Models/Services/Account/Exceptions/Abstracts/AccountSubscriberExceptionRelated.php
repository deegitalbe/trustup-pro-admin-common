<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\Abstracts;

use Deegitalbe\TrustupProAdminCommon\Contracts\ContextualContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountSubscriberContract;
use Exception;

abstract class AccountSubscriberExceptionRelated extends Exception implements ContextualContract
{
    /**
     * Subscriber service.
     * 
     * @var AccountSubscriberContract
     */
    protected $subscriber;

    /**
     * Constructing exception for given account.
     * 
     * @param AccountSubscriberContract $subscriber
     * @return static
     */
    public static function create(AccountSubscriberContract $subscriber)
    {
        return (new static())->setSubscriber($subscriber);
    }

    /**
     * Setting subscriber.
     * 
     * @param AccountSubscriberContract $subscriber
     * @return static
     */
    public function setSubscriber(AccountSubscriberContract $subscriber)
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    /**
     * Exception context.
     * 
     * @return array
     */
    public function context(): array
    {
        return $this->subscriber->context();
    }
}