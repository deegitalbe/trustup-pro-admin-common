<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;

class AppTest extends TestCase
{
    /**
     * Created app key.
     * 
     * @var string
     */
    protected $app_key = "timetracker";

    /**
     * Created plan name.
     * 
     * @var string
     */
    protected $plan_name = "plan_name";

    /** 
     * Created app.
     * 
     * @var AppContract
     */
    protected $app_model;

    /**
     * Created plan. 
     * 
     * @var PlanContract
    */
    protected $plan;
    
    /** 
     * Setting up test.
     * 
     * @return void
     */
    public function setup(): void
    {
        parent::setup();
        
        $this->createAppAndRelatedPlan();
    }

    /**
     * @test
     */
    public function app_model_adding_plan()
    {
        $this->assertTrue($this->appHavingPlans());
    }

    /**
     * @test
     */
    public function app_model_removing_plan()
    {
        $this->app_model->removePlan($this->plan)
            ->persist();

        $this->assertFalse($this->appHavingPlans());
    }

    /** @test */
    public function app_model_not_unlinking_unrelated_plan()
    {        
        $unrelated = $this->app_model->replicate()
            ->setKey("agenda")
            ->persist();

        $unrelated->removePlan($this->plan);

        $this->assertTrue($this->appHavingPlans());
    }

    /**
     * Creating app.
     * 
     * @return self
     */
    protected function createApp(): self
    {
        $this->app_model = app(AppContract::class)
            ->setKey($this->app_key)
            ->setUrl('https://agenda.trustup.pro')
            ->setPaid(true)
            ->setName('Un super agenda')
            ->setDescription('une super description')
            ->setAvailable(true)
            ->setTranslated(true)
            ->persist();

        return $this;
    }

    /**
     * Creating plan.
     * 
     * @return self
     */
    protected function createPlan(): self
    {
        $this->plan = app()->make(PlanContract::class)
            ->setName($this->plan_name)
            ->setTrialDuration(14)
            ->persist();

        return $this;
    }

    /**
     * Creating app, plan and linking them to one another.
     * 
     * @return self
     */
    protected function createAppAndRelatedPlan(): self
    {
        $this->createApp()
            ->createPlan();
        
        $this->app_model->addPlan($this->plan);

        return $this;
    }

    /**
     * Telling if app is having plans.
     * 
     * @return bool
     */
    protected function appHavingPlans(): bool
    {
        return $this->app_model->refresh()->getPlans()->isNotEmpty();
    }

}