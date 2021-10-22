<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Carbon\Carbon;
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
    public function account_setting_and_getting_synchronization_date()
    {
        $now = new Carbon('2020-10-10 10:00:00');
        $this->travelTo($now);
        $account = app(AccountContract::class)
            ->setSynchronizedAt(now());
        $this->travelBack();
        
        $this->assertTrue($account->fresh()->getSynchronizedAt()->eq($now));
    }

    /**
     * @test
     */
    public function account_setting_and_getting_initial_created_at_date()
    {
        $now = new Carbon('2020-10-10 10:00:00');
        $this->travelTo($now);
        $account = app(AccountContract::class)
            ->setInitialCreatedAt(now());
        $this->travelBack();
        
        $this->assertTrue($account->fresh()->getInitialCreatedAt()->eq($now));
    }

    /**
     * @test
     */
    public function account_setting_and_getting_raw()
    {
        $raw = ['name' => "Francis"];
        $account = app(AccountContract::class)
            ->setRaw($raw);
        
        $this->assertEquals($account->fresh()->getRaw(), $raw);
    }

    /**
     * @test
     */
    public function app_scope_not_dashboard()
    {
        $app_not_dashboard = app(AppContract::class)
            ->setKey('agenda')
            ->persist();
        
        $app_dashboard = app(AppContract::class)
            ->setKey(Package::app()::DASHBOARD)
            ->persist();
        
        $this->assertEquals(1, Package::app()::notDashboard()->count());
        $this->assertEquals("agenda", Package::app()::notDashboard()->first()->getKey());
    }

    /**
     * @test
     */
    public function account_scope_not_dashboard_excluding()
    {
        $app_dashboard = app(AppContract::class)
            ->setKey(Package::app()::DASHBOARD)
            ->persist();

        $account = app()->make(AccountContract::class)
            ->persist()
            ->setApp($app_dashboard);
        
        $this->assertEquals(0, Package::account()::notDashboard()->count());
    }

    /**
     * @test
     */
    public function account_scope_not_dashboard_including()
    {
        $app_not_dashboard = app(AppContract::class)
            ->setKey(':salam')
            ->persist();

        $account = app()->make(AccountContract::class)
            ->persist()
            ->setApp($app_not_dashboard);
        
        $this->assertEquals(1, Package::account()::notDashboard()->count());
        $this->assertEquals($account->id, Package::account()::notDashboard()->first()->id);
    }

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
    public function account_chargebee_status_getting_account()
    {
        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(Package::accountChargebee()::NON_RENEWING)
            ->setId('dlfkjqlsfjlsdkjfql')
            ->persist();
        
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist()
            ->setChargebee($chargebee);
        
        $this->assertEquals($account->id, $chargebee->fresh()->getAccount()->id);
    }

    /**
     * @test
     */
    public function account_deleting_status_when_setting_account_status_to_null()
    {
        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(Package::accountChargebee()::NON_RENEWING)
            ->setId('dlfkjqlsfjlsdkjfql');
        
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist()
            ->setChargebee($chargebee)
            ->setChargebee(null);
        
        $this->assertNull($account->fresh()->getChargebee());
        $this->assertEquals(0, Package::accountChargebee()::count());
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
    public function account_not_accessed_scope_excluding()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now())
            ->persist()
            ->setAccount($account);
        
        $this->assertEquals(0, $account->notAccessed()->count());
    }

    /**
     * @test
     */
    public function account_not_accessed_or_access_before_including_respecting_date()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->subDays(2))
            ->persist()
            ->setAccount($account);
        
        $this->assertEquals(1, Package::account()::notAccessedOrLastAccessBefore(now())->count());
    }

    /**
     * @test
     */
    public function account_scope_not_accessed_or_last_access_before_including_last_access_only()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->subDays(2))
            ->persist()
            ->setAccount($account);
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->addDays(2))
            ->persist()
            ->setAccount($account);
        
        $this->assertEquals(0, Package::account()::notAccessedOrLastAccessBefore(now())->count());
    }

    /**
     * @test
     */
    public function account_access_entries_scope_last_access_entry_getting_last_entry_by_account()
    {
        $account = app(AccountContract::class)
            ->setUuid('account_1')
            ->persist();
        
        $account_2 = app(AccountContract::class)
            ->setUuid('account_2')
            ->persist();
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->subDays(2))
            ->persist()
            ->setAccount($account);

        $access_entry_2 = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->subDays(1))
            ->persist()
            ->setAccount($account);
        
        $access_entry_3 = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->addDays(1))
            ->persist()
            ->setAccount($account_2);

        $access_entry_4 = app(AccountAccessEntryContract::class)
            ->setAccessAt(now()->addDays(3))
            ->persist()
            ->setAccount($account_2);

        $entry_by_accounts = Package::accountAccessEntry()::lastAccessEntryByAccount()->get();
        
        $this->assertEquals(2, $entry_by_accounts->count());
        $this->assertEquals(
            $access_entry_2->getAccessAt(),
            $entry_by_accounts->first()->getAccessAt()
        );
        $this->assertEquals(
            $access_entry_4->getAccessAt(),
            $entry_by_accounts[1]->getAccessAt()
        );
    }

    /**
     * @test
     */
    public function account_not_accessed_or_access_before_excluding_not_respecting_date()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();
        
        $access_entry = app(AccountAccessEntryContract::class)
            ->setAccessAt(now())
            ->persist()
            ->setAccount($account);
        
        $this->assertEquals(0, Package::account()::notAccessedOrLastAccessBefore(now()->subDays(2))->count());
    }

    /**
     * @test
     */
    public function account_not_accessed_or_access_before_including_respecting_not_accessed()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();
        
        $this->assertEquals(1, Package::account()::notAccessedOrLastAccessBefore(now())->count());
    }

    /**
     * @test
     */
    public function account_not_accessed_scope_including()
    {
        $account = app(AccountContract::class)
            ->setUuid('sdlfjslfj')
            ->persist();
        
        $this->assertEquals(1, $account->notAccessed()->count());
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