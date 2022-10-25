<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Henrotaym\LaravelModelQueries\Queries\Contracts\QueryContract;

/**
 * Query used to retrieve plans.
 */
interface PlanQueryContract extends QueryContract
{
    /**
     * Limiting to plans considered as defaults (monthly).
     * 
     * @return PlanQueryContract
     */
    public function beingDefault(): PlanQueryContract;

    /**
     * Limiting to plans considered as defaults yearly.
     * 
     * @return PlanQueryContract
     */
    public function beingYearlyDefault(): PlanQueryContract;

    /**
     * Limiting plans to yearly ones.
     * 
     * @return static
     */
    public function beingYearly(): PlanQueryContract;

    /**
     * Limiting plans to monthly ones.
     * 
     * @return static
     */
    public function beingMonthly(): PlanQueryContract;

    /**
     * Limiting plans to those matching given name.
     * 
     * @param string $name
     * @return PlanQueryContract
     */
    public function whereName(string $name): PlanQueryContract;

    /**
     * Limiting plans to those matching given app.
     * 
     * @param AppContract $app
     * @return PlanQueryContract
     */
    public function whereApp(AppContract $app): PlanQueryContract;

    /**
     * Limiting plans to global plans.
     * 
     * @param AppContract $app
     * @return PlanQueryContract
     */
    public function whereGlobal(): PlanQueryContract;

    /**
     * Limiting plans to those matching given app or global plans
     * 
     * @param AppContract $app
     * @return PlanQueryContract
     */
    public function whereAppOrGlobal(AppContract $app): PlanQueryContract;
}