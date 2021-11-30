<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use stdClass;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Tests\Models\ProfessionalTestModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AppQueryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account\AccountSetterByAppResponseContract;

class AccountSetterByAppResponseTest extends TestCase
{
    protected $chargebee_subscription_id = "test_chargebee_id";
    
    protected $chargebee_subscription_status = "trial";

    protected $account_uuid = "test_account_uuid";

    protected $app_model;

    protected $professional;

    protected $response;

    protected $setter;

    protected $mocked_setter = false;

    /**
     * @test
     */
    public function account_setter_by_app_response_getting_app_from_response()
    {
        $query = $this->mockThis(AppQueryContract::class)
            ->shouldReceive('whereKeyIs')
                ->once()
                ->andReturnSelf()
            ->shouldReceive('first')
                ->once()
                ->andReturn($this->getApp());

        $this->assertEquals($this->getApp()->getid(), $this->callPrivateMethod('getApp', $this->getSetter(), $this->getResponse())->getId());
    }

    /**
     * @test
     */
    public function account_setter_by_app_response_getting_app_from_app_setter()
    {
        $this->getSetter()->setApp($this->getApp());
        $query = $this->mockThis(AppQueryContract::class)
            ->shouldNotReceive('whereKeyIs');

        $this->assertEquals($this->getApp()->getId(), $this->callPrivateMethod('getApp', $this->getSetter(), $this->getResponse())->getId());
    }

    /**
     * @test
     */
    public function account_setter_by_app_response_getting_professional_from_response()
    {
        $this->assertEquals($this->getProfessional()->getId(), $this->callPrivateMethod('getProfessional', $this->getSetter(), $this->getResponse())->getId());
    }

    /**
     * @test
     */
    public function account_setter_by_app_response_getting_professional_from_setter()
    {
        $this->getSetter()->setProfessional($this->getProfessional());
        // By deleting we make sure we get an error if any database hit happens.
        $this->getProfessional()->delete();
        $this->assertEquals($this->getProfessional()->getId(), $this->callPrivateMethod('getProfessional', $this->getSetter(), $this->getResponse())->getId());
    }

    /**
     * @test
     */
    public function account_setter_by_app_response_storing_new_account()
    {
        $account = $this->getMockedSetter()->getAccount($this->getResponse());
        $this->assertModelExists($account);
    }

    /**
     * @test
     */
    public function account_setter_by_app_response_updating_inactive_account()
    {
        $inactive_account = $this->getInactiveAccount();
        $account = $this->getMockedSetter()->getAccount($this->getResponse());
        $inactive_account->refresh();
        
        $this->assertEquals($inactive_account->id, $account->id);
        $this->assertEquals($inactive_account->getUuid(), $account->getUuid());
        $this->assertEquals($account->getChargebee()->getId(), $this->chargebee_subscription_id);
        $this->assertEquals(1, Package::account()::count());
    }
    
    /**
     * @test
     */
    public function account_setter_by_app_response_setting_active_status_for_free_app()
    {
        // Making app free.
        $this->getApp()->setPaid(false)->persist();

        $account = $this->getMockedSetter()->getAccount($this->getResponse());
        $this->assertNull($account->getChargebee()->getId());
        $this->assertTrue($account->getChargebee()->isActive());
    }

    protected function getInactiveAccount(): AccountContract
    {
        return app()->make(AccountContract::class)
            ->setUuid(null)
            ->setApp($this->getApp())
            ->setProfessional($this->getProfessional())
            ->persist();
    }

    /**
     * For unknown reasons, I could not mock AccountSetterByAppResponseContract due to this error when calling getAccount
     * 
     *      $setter = $this->mockThis(AccountSetterByAppResponseContract::class)
     *          ->shouldReceive('getApp')
     *              ->once()
     *              ->andReturn($this->getApp())
     *          ->shouldReceive('getProfessional')
     *              ->once()
     *              ->andReturn($this->getProfessional())
     *          ->shouldReceive('getAccount')
     *              ->once()
     *              ->passthru()
     * 
     *  ERROR: call_user_func_array() expects parameter 1 to be a valid callback, cannot access parent:: when current class scope has no parent
     *  🤷‍♂️
     */
    protected function getMockedSetter(): AccountSetterByAppResponseContract
    {
        if (!$this->mocked_setter):
            // I was forced to set setter attributes to at least avoid database hit..
            $this->setPrivateProperty('app', $this->getApp(), $this->getSetter());
            $this->setPrivateProperty('professional', $this->getProfessional(), $this->getSetter());
            $this->mocked_setter = true;
        endif;

        return $this->getSetter();
    }

    protected function getSetter(): AccountSetterByAppResponseContract
    {
        if ($this->setter):
            return $this->setter;
        endif;

        return $this->setter = app()->make(AccountSetterByAppResponseContract::class);
    }

    protected function getResponse(): stdClass
    {
        if ($this->response):
            return $this->response;
        endif;

        return $this->response = (object) [
            'app_key' => $this->getApp()->getKey(),
            'authorization_key' => $this->getProfessional()->getAuthorizationKey(),
            'created_at' => now()->toDateTimeString(),
            'uuid' => $this->account_uuid,
            'chargebee_subscription_id' => $this->chargebee_subscription_id,
            'chargebee_subscription_status' => $this->chargebee_subscription_status,
        ];
    }

    protected function getApp(): AppContract
    {
        if ($this->app_model):
            return $this->app_model;
        endif;

        return $this->app_model = app()->make(AppContract::class)
            ->setKey('agenda')
            ->setUrl('https://agenda.trustup.pro')
            ->setPaid(true)
            ->setName('Un super agenda')
            ->setDescription('une super description')
            ->setAvailable(true)
            ->setTranslated(true)
            ->persist();
    }

    protected function getProfessional(): ProfessionalContract
    {
        if ($this->professional):
            return $this->professional;
        endif;

        return $this->professional = (new ProfessionalTestModel(['authorization_key' => "test", 'id' => 202]))->persist();
    }

}