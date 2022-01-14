<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Mockery;
use Mockery\MockInterface;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\ChargebeeClient\Chargebee\SubscriptionApi;
use Deegitalbe\TrustupProAdminCommon\Models\AccountChargebee;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\PlanQueryContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;

class AccountChargebeeTest extends TestCase
{
    
    /** @var MockInterface */
    protected $account_chargebee;

    /** @var MockInterface */
    protected $account;

    /** @var MockInterface */
    protected $app_model;
    
    /** @var MockInterface */
    protected $subscription;
    
    /** @var MockInterface */
    protected $subscription_plan;
    
    /** @var MockInterface */
    protected $plan_query;

    /** @var PlanContract */
    protected $plan;

    /** @test */
    public function account_chargebee_linking_plan()
    {
        $plan_name = 'test';

        $plan = app()->make(PlanContract::class)
            ->setName($plan_name);

        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(":test")
            ->setId(':dlfkjqlsfjlsdkjfql')
            ->setPlan($plan)
            ->persist();

        $this->assertEquals($plan_name, $chargebee->fresh()->getPlan()->getName());
    }

    /** @test */
    public function account_chargebee_unlinking_plan()
    {
        $plan_name = 'test';

        $plan = app()->make(PlanContract::class)
            ->setName($plan_name)
            ->persist();

        $chargebee = app(AccountChargebeeContract::class)
            ->setStatus(":test")
            ->setId(':dlfkjqlsfjlsdkjfql')
            ->setPlan($plan)
            ->persist()
            ->setPlan(null)
            ->persist();

        $this->assertNull($chargebee->fresh()->getPlan());
    }

    /** @test */
    public function account_chargebee_from_subscription_not_finding_plan()
    {
        // SETUP
        $this->mockSubscriptionPlan()
            ->mockSubscription()
            ->mockPlanQuery()
            ->mockAccountChargebee()
            ->mockAccount()
            ->mockApp();
        
        // EXPECTATIONS
        $this->subscription_plan->expects()->getId()->andReturn('plan_id');

        $this->subscription->expects()->getStatus()->andReturn('subscription_status');
        $this->subscription->expects()->getId()->andReturn('subscription_id');
        $this->subscription->expects()->getPlan()->andReturn($this->subscription_plan);

        $this->plan_query->expects()->whereName('plan_id')->andReturnSelf();
        $this->plan_query->expects()->whereApp($this->app_model)->andReturnSelf();
        $this->plan_query->expects()->first()->andReturnNull();

        $this->account->expects()->getApp()->andReturn($this->app_model);

        $this->account_chargebee->expects()->fromSubscription($this->subscription)->passthru();
        $this->account_chargebee->expects()->getAccount()->andReturn($this->account);
        $this->account_chargebee->expects()->setStatus('subscription_status')->andReturnSelf();
        $this->account_chargebee->expects()->setId('subscription_id')->andReturnSelf();

        // START
        $this->account_chargebee->fromSubscription($this->subscription);
    }

    /** @test */
    public function account_chargebee_from_subscription_finding_plan()
    {
        // SETUP
        $this->mockSubscriptionPlan()
            ->mockSubscription()
            ->mockPlanQuery()
            ->mockAccountChargebee()
            ->mockAccount()
            ->mockApp()
            ->mockPlan();
        
        // EXPECTATIONS
        $this->subscription_plan->expects()->getId()->andReturn('plan_id');

        $this->subscription->expects()->getStatus()->andReturn('subscription_status');
        $this->subscription->expects()->getId()->andReturn('subscription_id');
        $this->subscription->expects()->getPlan()->andReturn($this->subscription_plan);

        $this->plan_query->expects()->whereName('plan_id')->andReturnSelf();
        $this->plan_query->expects()->whereApp($this->app_model)->andReturnSelf();
        $this->plan_query->expects()->first()->andReturn($this->plan);

        $this->account->expects()->getApp()->andReturn($this->app_model);

        $this->account_chargebee->expects()->fromSubscription($this->subscription)->passthru();
        $this->account_chargebee->expects()->getAccount()->andReturn($this->account);
        $this->account_chargebee->expects()->setStatus('subscription_status')->andReturnSelf();
        $this->account_chargebee->expects()->setId('subscription_id')->andReturnSelf();
        $this->account_chargebee->expects()->setPlan($this->plan)->andReturnSelf();

        // START
        $this->account_chargebee->fromSubscription($this->subscription);
    }

    /** @test */
    public function account_chargebee_refresh_from_api_not_finding_subscription()
    {
        $subscription_api = $this->mockThis(SubscriptionApiContract::class);
        $account_chargebee = $this->mockThis(AccountChargebee::class);

        $subscription_api->expects()->find("test")->andReturnNull();
        $account_chargebee->expects()->refreshFromApi()->passthru();
        $account_chargebee->expects()->getId()->andReturn("test");
        $account_chargebee->expects()->fromSubscription()->times(0);
        $account_chargebee->expects()->getAccount()->times(0);

        $account_chargebee->refreshFromApi();
    }

    /** @test */
    public function account_chargebee_refresh_from_api_finding_subscription()
    {
        $subscription_api = $this->mockThis(SubscriptionApiContract::class);
        $account_chargebee = $this->mockThis(AccountChargebee::class);
        $account = $this->mockThis(AccountContract::class);
        $subscription = $this->mockThis(SubscriptionContract::class);

        $subscription_api->expects()->find("test")->andReturn($subscription);
        
        $account_chargebee->expects()->refreshFromApi()->passthru();
        $account_chargebee->expects()->getId()->andReturn("test");
        $account_chargebee->expects()->fromSubscription($subscription)->andReturnSelf();
        $account_chargebee->expects()->persist()->andReturnSelf();
        $account_chargebee->expects()->getAccount()->andReturn($account);
        
        $account->expects()->updateInApp();

        $account_chargebee->refreshFromApi();
    }

    /**
     * Mocking subscription.
     * 
     * @return self
     */
    protected function mockSubscription(): self
    {
        $this->subscription = $this->mockThis(SubscriptionContract::class);

        return $this;
    }

    /**
     * Mocking subscription plan.
     * 
     * @return self
     */
    protected function mockSubscriptionPlan(): self
    {
        $this->subscription_plan = $this->mockThis(SubscriptionPlanContract::class);

        return $this;
    }
    
    /**
     * Mocking plan query.
     * 
     * @return self
     */
    protected function mockPlanQuery(): self
    {
        $this->plan_query = $this->mockThis(PlanQueryContract::class);

        return $this;
    }

    /**
     * Mocking concrete account chargebee.
     * 
     * @return self
     */
    protected function mockAccountChargebee(): self
    {
        $this->account_chargebee = $this->mockThis(Package::accountChargebee());

        return $this;
    }

    /**
     * Mocking app plan.
     * 
     * @return self
     */
    protected function mockPlan(): self
    {
        $this->plan = $this->mockThis(PlanContract::class);

        return $this;
    }

    /**
     * Mocking app.
     * 
     * @return self
     */
    protected function mockApp(): self
    {
        $this->app_model = $this->mockThis(AppContract::class);

        return $this;
    }

    /**
     * Mocking account.
     * 
     * @return self
     */
    protected function mockAccount(): self
    {
        $this->account = $this->mockThis(AccountContract::class);

        return $this;
    }
}