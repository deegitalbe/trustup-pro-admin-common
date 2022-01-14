<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Mockery\MockInterface;
use Deegitalbe\TrustupProAdminCommon\Models\App;
use Deegitalbe\TrustupProAdminCommon\Models\Plan;
use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\ChargebeeClient\Chargebee\Models\SubscriptionPlan;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionPlanApiContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;

class AccountTest extends TestCase
{
    /** @test */
    public function account_model_not_refreshing_if_not_having_chargebee()
    {
        $this->mockAccount();

        $this->account->expects()->refreshChargebee()->passthru();
        $this->account->expects()->hasChargebee()->andReturn(false);
        $this->account->expects()->getChargebee()->times(0);
        $this->account->expects()->refresh()->times(0);

        $this->account->refreshChargebee();
    }

    /** @test */
    public function account_model_refreshing_if_having_chargebee()
    {
        $this->mockAccount()
            ->mockAccountChargebee();

        $this->account->expects()->refreshChargebee()->passthru();
        $this->account->expects()->hasChargebee()->andReturn(true);
        $this->account->expects()->getChargebee()->andReturn($this->account_chargebee);
        $this->account->expects()->refresh()->andReturnSelf();

        $this->account_chargebee->expects()->refreshFromApi();

        $this->account->refreshChargebee();
    }

    /**
     * Account chargebee.
     * 
     * @var MockInterface
     */
    protected $account_chargebee;

    /**
     * Account.
     * 
     * @var MockInterface
     */
    protected $account;

    /** 
     * Mocking account chargebee.
     * 
     * @return self
     */
    protected function mockAccountChargebee(): self
    {
        $this->account_chargebee = $this->mockThis(AccountChargebeeContract::class);

        return $this;
    }

    /**
     * Mocking account.
     * 
     * @return self
     */
    protected function mockAccount(): self
    {
        $this->account = $this->mockThis(Account::class);

        return $this;
    }

}