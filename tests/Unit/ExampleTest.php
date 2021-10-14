<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\App\AppClientContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryUserContract;

class ExampleTest extends TestCase
{
    /**
     * @test
     */
    public function saving_all_models()
    {
        $app = app(AppContract::class)
            ->setKey('agenda')
            ->setUrl('https://agenda.trustup.pro')
            ->setPaid(true)
            ->setName('Un super agenda')
            ->setDescription('une super description')
            ->setAvailable(true)
            ->setTranslated(true)
            ->persist();

        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus('in_trial')
            ->setId('dlfkjqlsfjlsdkjfql');

        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist()
            ->setApp($app)
            ->setChargebee($chargebee);

        $access_entry_user = app(AccountAccessEntryUserContract::class)
            ->setFirstName('Florian')
            ->setLastName('Husquinet')
            ->setId(12)
            ->setAvatar('https://picsum.photos/200/300');
            
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now())
            ->persist()
            ->setAccount($account)
            ->setUser($access_entry_user);

        $this->assertEquals($app->refresh()->id, $account->refresh()->getApp()->id);
        $this->assertEquals($chargebee->getId(), $account->getChargebee()->getId());
        $this->assertEquals($access_entry_user->getId(), $access_entry->refresh()->getUser()->getId());
        $this->assertCount(1, $account->getAccountAccessEntries());
        $this->assertEquals($account->getAccountAccessEntries()->first()->id, $access_entry->id);
    }

    /**
     * @test
     */
    public function app_client_getting_app_accounts()
    {
        $app = app(AppContract::class)
            ->setKey('agenda')
            ->setUrl('https://agenda.trustup.pro')
            ->setPaid(true)
            ->setName('Un super agenda')
            ->setDescription('une super description')
            ->setAvailable(true)
            ->setTranslated(true)
            ->persist();

        $this->mock(AppClientContract::class)
            ->shouldReceive('getAllAccounts')
                ->with()
                ->andReturn(collect())
            ->shouldReceive('setApp')
                ->withArgs(function($arg1) use ($app) {
                    return $arg1->id === $app->id;
                })
                ->andReturnSelf();

        $this->assertCount(0, $app->getClient()->getAllAccounts());
    }

    /**
     * @test
     */
    public function account_getting_last_access_entry()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();

        $access_entry_user = app(AccountAccessEntryUserContract::class)
            ->setFirstName('Florian')
            ->setLastName('Husquinet')
            ->setId(12)
            ->setAvatar('https://picsum.photos/200/300');
            
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now())
            ->persist()
            ->setAccount($account)
            ->setUser($access_entry_user);
        
        $this->assertEquals(Package::account()::first()->getLastAccountAccessEntry()->id, $access_entry->id);
    }

    /**
     * @test
     */
    public function account_getting_last_access_at()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();

        $access_entry_user = app(AccountAccessEntryUserContract::class)
            ->setFirstName('Florian')
            ->setLastName('Husuinet')
            ->setId(12)
            ->setAvatar('https://picsum.photos/200/300');
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now())
            ->persist()
            ->setAccount($account)
            ->setUser($access_entry_user);
        
        $this->assertTrue($account->fresh()->getLastAccessAt()->eq($access_entry->getAccessAt()));
    }

    /**
     * @test
     */
    public function account_access_entry_accessed_at_least_at_scope_not_getting_element_accessed_before_given_date()
    {
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->subDays(2))
            ->persist();
        
        $this->assertCount(0, Package::accountAccessEntry()::accessedAtLeastAt(now())->get());
    }

    /**
     * @test
     */
    public function account_access_entry_accessed_at_least_at_scope_not_getting_element_accessed_after_given_date()
    {
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now())
            ->persist();
        
        $this->assertCount(1, Package::accountAccessEntry()::accessedAtLeastAt(now()->subDays(2))->get());
    }

    /**
     * @test
     */
    public function account_access_entry_accessed_at_least_at_scope_not_getting_element_accessed_at_given_date()
    {
        $now = now();
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt($now)
            ->persist();
        
        $this->assertCount(1, Package::accountAccessEntry()::accessedAtLeastAt($now)->get());
    }
}