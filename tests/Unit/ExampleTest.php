<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryUserContract;

class ExampleTest extends TestCase
{
    /**
     * @test
     */
    public function returning_true()
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
    public function getting_app_accounts()
    {
        // Override authorization key before making this test.
        // config ([ Package::prefix() . '.authorization' => '']);
        
        $app = app(AppContract::class)
            ->setKey('agenda')
            ->setUrl('https://agenda.trustup.pro')
            ->setPaid(true)
            ->setName('Un super agenda')
            ->setDescription('une super description')
            ->setAvailable(true)
            ->setTranslated(true)
            ->persist();

        $this->assertNotNull($app->getClient()->getAllAccounts());
    }
}