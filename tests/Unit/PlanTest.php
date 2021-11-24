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

}