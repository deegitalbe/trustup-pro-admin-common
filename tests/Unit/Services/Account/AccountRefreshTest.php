<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit\Services\Account;

use Carbon\Carbon;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Mockery\MockInterface;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Models\Services\Account\AccountRefresh;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Services\Account\AccountRefreshContract;

class AccountRefreshTest extends TestCase
{
    /** @test */
    public function account_refresh_setting_app()
    {
        $this->setService()
            ->mockApp();

        $this->assertInstanceOfService($this->service->setApp($this->mocked_app));
        $this->assertEquals($this->mocked_app, $this->getPrivateProperty('app', $this->service));
    }

    /** @test */
    public function account_refresh_getting_app()
    {
        $this->setService()
            ->mockApp();

        $this->setPrivateProperty('app', $this->mocked_app, $this->service);
        $this->assertEquals($this->mocked_app, $this->service->getApp());
    }

    /** @test */
    public function account_refresh_setting_professional()
    {
        $this->setService()
            ->mockProfessional();

        $this->assertInstanceOfService($this->service->setProfessional($this->mocked_professional));
        $this->assertEquals($this->mocked_professional, $this->getPrivateProperty('professional', $this->service));
    }

    /** @test */
    public function account_refresh_getting_professional()
    {
        $this->setService()
            ->mockProfessional();

        $this->setPrivateProperty('professional', $this->mocked_professional, $this->service);
        $this->assertEquals($this->mocked_professional, $this->service->getProfessional());
    }

    /** @test */
    public function account_refresh_refreshing_common_account_attributes()
    {
        $this->mockService()
            ->mockProfessional()
            ->mockApp()
            ->mockNow()
            ->mockAccount();

        $this->mocked_account->expects()->setDeletedAt(null);
        $this->mocked_account->expects()->setSynchronizedAt()->withArgs(function($date) { return $date->eq($this->mocked_now); })->andReturnSelf();
        $this->mocked_account->expects()->setApp($this->mocked_app);
        $this->mocked_account->expects()->setProfessional($this->mocked_professional);
        $this->mocked_account->expects()->setInitialCreatedAt($this->mocked_now);

        $this->mocked_service->expects()->getApp()->andReturn($this->mocked_app);
        $this->mocked_service->expects()->getprofessional()->times(2)->andReturn($this->mocked_professional);
        $this->mocked_service->expects()->refreshCommonAttributes($this->mocked_account)->passthru();

        $this->mocked_professional->expects()->getCreatedAt()->andReturn($this->mocked_now);

        $this->mocked_service->refreshCommonAttributes($this->mocked_account);
    }


    /**
     * Asserting given response is an instance of service
     * 
     * @param mixed $response
     * @return self
     */
    protected function assertInstanceOfService($response): self
    {
        $this->assertInstanceOf(AccountRefresh::class, $response);

        return $this;
    }

    /**
     * Service
     * 
     * @var AccountRefresh
     */
    protected $service;

    /**
     * Setting service
     * 
     * @@return self
     */
    protected function setService(): self
    {
        $this->service = $this->app->make(AccountRefreshContract::class);
        
        return $this;
    }

    /**
     * Mocked service
     * 
     * @var MockInterface
     */
    protected $mocked_service;

    /**
     * Setting mocked_service
     * 
     * @@return self
     */
    protected function mockService(): self
    {
        $this->mocked_service = $this->mockThis(AccountRefresh::class);
        
        return $this;
    }

    /**
     * Mocked account
     * 
     * @var MockInterface
     */
    protected $mocked_account;

    /**
     * Setting mocked_account
     * 
     * @@return self
     */
    protected function mockAccount(): self
    {
        $this->mocked_account = $this->mockThis(AccountContract::class);
        
        return $this;
    }

    /**
     * Mocked now helper value
     * 
     * @var Carbon
     */
    protected $mocked_now;

    /**
     * Setting mocked now value
     * 
     * @@return self
     */
    protected function mockNow(): self
    {
        $this->mocked_now = now();
        $this->mockCarbonNow($this->mocked_now);
        
        return $this;
    }

    /**
     * Mocked app.
     * 
     * @var MockInterface
     */
    protected $mocked_app;

    /**
     * Setting mocked_app
     * 
     * @@return self
     */
    protected function mockApp(): self
    {
        $this->mocked_app = $this->mockThis(AppContract::class);
        
        return $this;
    }

    /**
     * Mocked professional.
     * 
     * @var MockInterface
     */
    protected $mocked_professional;

    /**
     * Setting mocked_professional
     * 
     * @@return self
     */
    protected function mockProfessional(): self
    {
        $this->mocked_professional = $this->mockThis(ProfessionalContract::class);
        
        return $this;
    }
}