<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Deegitalbe\TrustupProAdminCommon\Models\App;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;

class PlanTest extends TestCase
{
    /**
     * @test
     */
    public function plan_model_persistable()
    {
        $name = "test-plan";
        $plan = app()->make(PlanContract::class)
            ->setName($name)
            ->setTrialDuration(14);

        $app = app(AppContract::class)
            ->setKey('agenda')
            ->setUrl('https://agenda.trustup.pro')
            ->setPaid(true)
            ->setName('Un super agenda')
            ->setDescription('une super description')
            ->setAvailable(true)
            ->setTranslated(true)
            ->addPlan($plan)
            ->persist();

        $this->assertEquals($name, App::find($app->getId())->getPlans()->first()->getName());
    }

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

}