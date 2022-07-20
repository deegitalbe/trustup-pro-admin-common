<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit\Query;

use Mockery\MockInterface;
use Illuminate\Database\Eloquent\Builder;
use Deegitalbe\TrustupProAdminCommon\Models\Query\AccountQuery;
use Deegitalbe\TrustupProAdminCommon\Tests\NotUsingDatabaseTestCase;

class AccountQueryTest extends NotUsingDatabaseTestCase
{
    /** @test */
    public function account_query_getting_inactive_accounts()
    {
        $this->mockAccountQuery()
            ->mockBuilder()
            ->assertGettingBuilder();

        $this->account_query_mock->expects()->inactive()->passthru();

        $this->builder_mock->expects()->whereNull('uuid');

        $this->assertInstanceOfAccountQuery($this->account_query_mock->inactive());
    }

    /** @test */
    public function account_query_getting_active_accounts()
    {
        $this->mockAccountQuery()
            ->mockBuilder()
            ->assertGettingBuilder();

        $this->account_query_mock->expects()->active()->passthru();

        $this->builder_mock->expects()->whereNotNull('uuid');

        $this->assertInstanceOfAccountQuery($this->account_query_mock->active());
    }

    /** @var MockInterface|AccountQuery */
    protected $account_query_mock;
    
    /** 
     * Mocking account query.
     * 
     * @return self
     */
    protected function mockAccountQuery(): self
    {
        $this->account_query_mock = $this->mockThis(AccountQuery::class);

        return $this;
    }

    /** @var MockInterface|Builder */
    protected $builder_mock;

    /**
     * Mocking builder.
     * 
     * @return self
     */
    protected function mockBuilder(): self
    {
        $this->builder_mock = $this->mockThis(Builder::class);

        return $this;
    }
    
    /**
     * Making sure account query requested given time builder.
     * 
     * @param int $count Times query should be requested.
     * @return self
     */
    protected function assertGettingBuilder(int $count = 1): self
    {
        $this->account_query_mock->expects()->getQuery()->andReturn($this->builder_mock)->times($count);
        return $this;
    }

    /**
     * Asserting response is an account query.
     * 
     * @param mixed $response Element to assert.
     * @return self
     */
    protected function assertInstanceOfAccountQuery($response): self
    {
        $this->assertInstanceOf(AccountQuery::class, $response);

        return $this;
    }
}