<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Deegitalbe\TrustupProAdminCommon\Models\App;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;

class AppTest extends TestCase
{
    /**
     * @test
     */
    public function app_model_adding_plan()
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

    /**
     * @test
     */
    public function app_model_removing_plan()
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

        $app->removePlan($plan)
            ->persist();

        $this->assertTrue(App::find($app->getId())->getPlans()->isEmpty());
    }

}