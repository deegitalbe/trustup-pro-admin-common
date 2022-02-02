<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Mockery\MockInterface;
use Deegitalbe\TrustupProAdminCommon\Models\App;
use Deegitalbe\TrustupProAdminCommon\Models\Plan;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\ChargebeeClient\Chargebee\Models\SubscriptionPlan;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionPlanApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Subscription;
use Deegitalbe\TrustupProAdminCommon\Tests\NotUsingDatabaseTestCase;

class PlanTest extends NotUsingDatabaseTestCase
{
    /** @test */
    public function plan_model_telling_if_having_trial_duration()
    {
        $plan = app()->make(PlanContract::class)
            ->setName('test')
            ->setTrialDuration(14);

        $this->assertTrue($plan->hasTrialDuration());
    }

    /** @test */
    public function plan_model_telling_if_not_having_trial_duration()
    {
        $plan = app()->make(PlanContract::class)
            ->setName('test')
            ->setTrialDuration(0);

        $this->assertFalse($plan->hasTrialDuration());
    }

    /** @test */
    public function plan_model_setting_price_in_cent()
    {
        $cent_price = 1000;
        $plan = app()->make(PlanContract::class)
            ->setName('test')
            ->setPriceInCent($cent_price);

        $this->assertEquals($cent_price, $plan->getPriceInCent());
        $this->assertEquals($cent_price / 100, $plan->getPriceInEuro());
    }

    /** @test */
    public function plan_model_setting_price_in_euro()
    {
        $euro_price = 10;
        $plan = app()->make(PlanContract::class)
            ->setName('test')
            ->setPriceInEuro($euro_price);

        $this->assertEquals($euro_price, $plan->getPriceInEuro());
        $this->assertEquals($euro_price * 100, $plan->getPriceInCent());
    }

    /** @test */
    public function plan_model_setting_attributes_from_subscription_plan()
    {
        $this->mockAppPlan()
            ->mockSubscriptionPlan();

        $this->subscription_plan->expects()->getTrialDuration()->andReturn(10);
        $this->subscription_plan->expects()->getId()->andReturn('id');
        $this->subscription_plan->expects()->getPriceInCent()->andReturn(2000);

        $this->app_plan->expects()->fromSubscriptionPlan($this->subscription_plan)->passthru();
        $this->app_plan->expects()->setName('id')->andReturnSelf();
        $this->app_plan->expects()->setPriceInCent(2000)->andReturnSelf();
        $this->app_plan->expects()->setTrialDuration(10)->andReturnSelf();

        $this->app_plan->fromSubscriptionPlan($this->subscription_plan);
    }

    /** @test */
    public function plan_model_not_refreshing_if_no_plan_found()
    {
        $this->mockAppPlan()
            ->mockSubscriptionPlanApi();

        $this->subscription_plan_api->expects()->find("plan_name")->andReturnNull();

        $this->app_plan->expects()->refreshFromApi()->passthru();
        $this->app_plan->expects()->getName()->andReturn("plan_name");
        $this->app_plan->expects()->fromSubscriptionPlan()->times(0);
        $this->app_plan->expects()->persist()->times(0);

        $this->app_plan->refreshFromApi();
    }

    /** @test */
    public function plan_model_refreshing_if_plan_found()
    {
        $this->mockAppPlan()
            ->mockSubscriptionPlan()
            ->mockSubscriptionPlanApi();

        $this->subscription_plan_api->expects()->find("plan_name")->andReturn($this->subscription_plan);

        $this->app_plan->expects()->refreshFromApi()->passthru();
        $this->app_plan->expects()->getName()->andReturn("plan_name");
        $this->app_plan->expects()->fromSubscriptionPlan($this->subscription_plan)->andReturnSelf();
        $this->app_plan->expects()->persist();

        $this->app_plan->refreshFromApi();
    }

    /** @test */
    public function plan_transforming_to_subscription_plan()
    {
        $this->mockAppPlan()
            ->mockSubscriptionPlan();

        $id = ":id";
        $trial = 20;
        $price = 2000;

        $this->subscription_plan->expects()->setId($id)->andReturnSelf();
        $this->subscription_plan->expects()->setTrialDuration($trial)->andReturnSelf();
        $this->subscription_plan->expects()->setPriceInCent($price)->andReturnSelf();

        $this->app_plan->expects()->getName()->andReturn($id);
        $this->app_plan->expects()->getTrialDuration()->andReturn($trial);
        $this->app_plan->expects()->getPriceInCent()->andReturn($price);
        $this->app_plan->expects()->toSubscriptionPlan()->passthru();

        $this->assertInstanceOf(SubscriptionPlanContract::class, $this->app_plan->toSubscriptionPlan());
    }

    /**
     * Subscription plan api.
     * 
     * @var MockInterface
     */
    protected $subscription_plan_api;

    /**
     * Subscription plan.
     * 
     * @var MockInterface
     */
    protected $subscription_plan;

    /**
     * App plan.
     * 
     * @var MockInterface
     */
    protected $app_plan;

    /** 
     * Mocking subscription plan api.
     * 
     * @return self
     */
    protected function mockSubscriptionPlanApi(): self
    {
        $this->subscription_plan_api = $this->mockThis(SubscriptionPlanApiContract::class);

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
     * Mocking plan.
     * 
     * @return self
     */
    protected function mockAppPlan(): self
    {
        $this->app_plan = $this->mockThis(Plan::class);

        return $this;
    }

}