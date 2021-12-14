<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Deegitalbe\TrustupProAdminCommon\Models\App;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;

class PlanTest extends TestCase
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

}