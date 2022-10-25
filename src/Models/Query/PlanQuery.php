<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Query;

use Illuminate\Database\Eloquent\Builder;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Henrotaym\LaravelModelQueries\Queries\Abstracts\AbstractQuery;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AppQueryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\PlanQueryContract;

/**
 * Query used to retrieve plans.
 */
class PlanQuery extends AbstractQuery implements PlanQueryContract
{
    /**
     * Model linked to this query.
     * 
     * @return string
     */
    public function getModel(): string
    {
        return Package::plan();
    }

    /**
     * Limiting to plans considered as defaults (monthly).
     * 
     * @return PlanQueryContract
     */
    public function beingDefault(): PlanQueryContract
    {
        $this->getQuery()->where('is_default_plan', true);

        return $this;
    }

    /**
     * Limiting to plans considered as defaults yearly.
     * 
     * @return PlanQueryContract
     */
    public function beingYearlyDefault(): PlanQueryContract
    {
        $this->getQuery()->where('is_default_yearly_plan', true);

        return $this;
    }

    /**
     * Limiting plans to yearly ones.
     * 
     * @return static
     */
    public function beingYearly(): PlanQueryContract
    {
        return $this->getQuery()->where('is_yearly', true);
    }

    /**
     * Limiting plans to monthly ones.
     * 
     * @return static
     */
    public function beingMonthly(): PlanQueryContract
    {
        return $this->getQuery()->where('is_yearly', false);
    }

    /**
     * Limiting plans to those matching given name.
     * 
     * @param string $name
     * @return PlanQueryContract
     */
    public function whereName(string $name): PlanQueryContract
    {
        $this->getQuery()->where('name', $name);

        return $this;
    }

    /**
     * Limiting plans to those matching given app.
     * 
     * @param AppContract $app
     * @return PlanQueryContract
     */
    public function whereApp(AppContract $app): PlanQueryContract
    {
        $this->getQuery()->whereHas('app', function(Builder $builder) use ($app) {
            $query = app()->make(AppQueryContract::class);
            $query->setQuery($builder)
                ->whereKeyIs($app->getKey());
        });

        return $this;
    }

    /**
     * Limiting plans to global plans.
     * 
     * @param AppContract $app
     * @return PlanQueryContract
     */
    public function whereGlobal(): PlanQueryContract
    {
        return $this->getQuery()->whereNull('app_id');
    }

    /**
     * Limiting plans to those matching given app or global plans (app_id = null).
     * 
     * @param AppContract $app
     * @return PlanQueryContract
     */
    public function whereAppOrGlobal(AppContract $app): PlanQueryContract
    {
        $this->getQuery()->where(function(Builder $builder) use ($app) {
            $builder->whereHas('app', function(Builder $builder) use ($app) {
                $query = app()->make(AppQueryContract::class);
                $query->setQuery($builder)
                    ->whereKeyIs($app->getKey());
            })->orWhereNull('app_id');
        });

        return $this;
    }
}
