<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit\Services\Account\Exceptions;

use Mockery\MockInterface;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Exceptions\AppBeingFree;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\Contracts\AccountSubscriberContract;
use Deegitalbe\TrustupProAdminCommon\Tests\NotUsingDatabaseTestCase;

class AppBeingFreeTest extends NotUsingDatabaseTestCase
{
    /** @test */
    public function app_being_free_setting_subscriber()
    {
        $this->setException()
            ->mockSubscriber();
        $this->assertInstanceOf(AppBeingFree::class, $this->exception->setSubscriber($this->mocked_subscriber));
        $this->assertEquals($this->mocked_subscriber, $this->getPrivateProperty('subscriber', $this->exception));
    }

    /** @test */
    public function app_being_free_getting_context()
    {
        $this->mockException();
        
        $this->mocked_exception->expects()->context()->passthru();
        $this->mocked_subscriber->expects()->context()->andReturn($this->subscriber_expected_context);
        
        $this->assertEquals($this->subscriber_expected_context, $this->mocked_exception->context());
    }

    /** @var array */
    protected $subscriber_expected_context = ['hello' => "world"];

    /** @var AppBeingFree */
    protected $exception;

    /**
     * Mocking account
     * 
     * @return self
     */
    protected function setException(): self
    {
        $this->exception = new AppBeingFree();

        return $this;
    }

    /** @var AppBeingFree|MockInterface */
    protected $mocked_exception;

    /**
     * Mocking account
     * 
     * @return self
     */
    protected function mockException(): self
    {
        $this->mockSubscriber()
            ->mocked_exception = $this->mockThis(AppBeingFree::class);

        $this->setPrivateProperty('subscriber', $this->mocked_subscriber, $this->mocked_exception);

        return $this;
    }

    /** @var AccountSubscriberContract|MockInterface */
    protected $mocked_subscriber;

    /**
     * Mocking account
     * 
     * @return self
     */
    protected function mockSubscriber(): self
    {
        $this->mocked_subscriber = $this->mockThis(AccountSubscriberContract::class);

        return $this;
    }
}