<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Models\AccountChargebee;
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
    public function account_chargebee_status_where_status_scope()
    {
        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(Package::accountChargebee()::TRIAL)
            ->setId('dlfkjqlsfjlsdkjfql')
            ->persist();
        
        $this->assertCount(1, Package::accountChargebee()::whereStatus(Package::accountChargebee()::TRIAL)->get());
    }

    /**
     * @test
     */
    public function account_chargebee_status_in_trial_scope()
    {
        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(Package::accountChargebee()::TRIAL)
            ->setId('dlfkjqlsfjlsdkjfql')
            ->persist();
        
        $this->assertCount(1, Package::accountChargebee()::inTrial()->get());
    }

    /**
     * @test
     */
    public function account_chargebee_status_active_scope()
    {
        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(Package::accountChargebee()::ACTIVE)
            ->setId('dlfkjqlsfjlsdkjfql')
            ->persist();
        
        $this->assertCount(1, Package::accountChargebee()::active()->get());
    }

    /**
     * @test
     */
    public function account_chargebee_status_cancelled_scope()
    {
        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(Package::accountChargebee()::CANCELLED)
            ->setId('dlfkjqlsfjlsdkjfql')
            ->persist();
        
        $this->assertCount(1, Package::accountChargebee()::cancelled()->get());
    }

    /**
     * @test
     */
    public function account_chargebee_status_non_renewing_scope()
    {
        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(Package::accountChargebee()::NON_RENEWING)
            ->setId('dlfkjqlsfjlsdkjfql')
            ->persist();
        
        $this->assertCount(1, Package::accountChargebee()::nonRenewing()->get());
    }

    /**
     * @test
     */
    public function account_having_in_trial_status_scope()
    {
        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(Package::accountChargebee()::TRIAL)
            ->setId('dlfkjqlsfjlsdkjfql')
            ->persist();

        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist()
            ->setChargebee($chargebee);
        
        $this->assertCount(1, Package::account()::havingTrialStatus()->get());
    }

    /**
     * @test
     */
    public function account_chargebee_status_getting_accounts()
    {
        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(Package::accountChargebee()::NON_RENEWING)
            ->setId('dlfkjqlsfjlsdkjfql')
            ->persist();
        
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist()
            ->setChargebee($chargebee);
        
        $this->assertCount(1, $chargebee->fresh()->getAccounts());
    }

    /**
     * @test
     */
    public function account_access_entry_user_getting_account_access_entry()
    {
        $access_entry_user = app(AccountAccessEntryUserContract::class)
            ->setFirstName('Florian')
            ->setLastName('Husquinet')
            ->setId(12)
            ->setAvatar('https://picsum.photos/200/300');
            
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now())
            ->persist()
            ->setUser($access_entry_user);
        
        $this->assertEquals($access_entry->fresh()->id, $access_entry->fresh()->getUser()->getAccountAccessEntry()->id);
        $this->assertNull($access_entry_user->fresh());
    }

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

        $this->mockThis(AppClientContract::class)
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
    public function account_access_entry_accessed_at_least_at_scope_getting_element_accessed_after_given_date()
    {
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now())
            ->persist();
        
        $this->assertCount(1, Package::accountAccessEntry()::accessedAtLeastAt(now()->subDays(2))->get());
    }

    /**
     * @test
     */
    public function account_access_entry_accessed_at_least_at_scope_getting_element_accessed_at_given_date()
    {
        $now = now();
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt($now)
            ->persist();
        
        $this->assertCount(1, Package::accountAccessEntry()::accessedAtLeastAt($now)->get());
    }

    /**
     * @test
     */
    public function account_accessed_at_least_at_scope_getting_element_accessed_after_given_date()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now())
            ->persist()
            ->setAccount($account);
        
        $this->assertCount(1, Package::account()::accessedAtLeastAt(now()->subDays(2))->get());
    }

    /**
     * @test
     */
    public function account_access_entry_accessed_before_scope_getting_element_accessed_before_given_date()
    {
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->subDays(2))
            ->persist();
        
        $this->assertCount(1, Package::accountAccessEntry()::accessedBefore(now())->get());
    }

    /**
     * @test
     */
    public function account_access_entry_accessed_before_scope_not_getting_element_accessed_after_given_date()
    {
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->addDays(2))
            ->persist();
        
        $this->assertCount(0, Package::accountAccessEntry()::accessedBefore(now())->get());
    }

    /**
     * @test
     */
    public function account_access_entry_accessed_before_scope_not_getting_element_accessed_at_given_date()
    {
        $now = now();
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt($now)
            ->persist();
        
        $this->assertCount(0, Package::accountAccessEntry()::accessedBefore($now)->get());
    }

    /**
     * @test
     */
    public function account_accessed_before_scope_getting_element_accessed_before_given_date()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->subDays(2))
            ->persist()
            ->setAccount($account);
        
        $this->assertCount(1, Package::account()::lastAccessBefore(now())->get());
    }
}