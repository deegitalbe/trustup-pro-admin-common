<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Mockery\MockInterface;
use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Models\AccountChargebee;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;

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

        $this->account_chargebee->expects()->refreshFromApi(false);

        $this->account->refreshChargebee();
    }

    /** @test */
    public function account_model_setting_chargebee()
    {
        // Creating two statuses.
        $account_chargebee_first = $this->app->make(AccountChargebeeContract::class)
            ->setId('first')
            ->persist();

        $account_chargebee_second = $this->app->make(AccountChargebeeContract::class)
            ->setId('second')
            ->persist();

        // Making sure two statuses were persisted.
        $this->assertEquals(2, $this->countAccountChargebee());

        // Linking account to first status
        $account = $this->app->make(AccountContract::class)
            ->persist()
            ->setChargebee($account_chargebee_first);

        // Making sure account is related to first status
        $this->assertAccountIsRelatedToAccountChargebee($account, $account_chargebee_first);
        // Making sure no status were deleted.
        $this->assertEquals(2, $this->countAccountChargebee());

        // Linking account to second status.
        $account->setChargebee($account_chargebee_second);
        
        // Making sure account is linked to second status
        $this->assertAccountIsRelatedToAccountChargebee($account, $account_chargebee_second);
        // Making sure first status was deleted
        $this->assertEquals(1, $this->countAccountChargebee());
    }

    /**
     * Making sure given account is linked to given account status.
     * 
     * @param AccountContract $account
     * @param AccountChargebeeContract $account_chargebee
     * @return self
     */
    protected function assertAccountIsRelatedToAccountChargebee(AccountContract $account, AccountChargebeeContract $account_chargebee): self
    {
        $this->assertEquals($account_chargebee->getId(), $account->fresh()->getChargebee()->getId());

        return $this;
    }

    /**
     * Counting account chargebee models.
     * 
     * @return int
     */
    protected function countAccountChargebee(): int
    {
        return AccountChargebee::count();
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