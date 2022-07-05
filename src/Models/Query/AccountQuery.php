<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Query;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Henrotaym\LaravelModelQueries\Queries\Abstracts\AbstractQuery;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AccountQueryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AccountChargebeeQueryContract;

/**
 * Query used to retrieve accounts.
 */
class AccountQuery extends AbstractQuery implements AccountQueryContract
{
    /**
     * Model linked to this query.
     * 
     * @return string
     */
    public function getModel(): string
    {
        return Package::account();
    }

    /**
     * AccountChargebee query.
     * 
     * @return AccountChargebeeQueryContract
     */
    protected function getAccountChargebeeQuery(): AccountChargebeeQueryContract
    {
        return app()->make(AccountChargebeeQueryContract::class);
    }

    /**
     * Limiting accounts to those linked to given app.
     * 
     * @param AppContract $app
     * @return AccountQueryContract
     */
    public function whereApp(AppContract $app): AccountQueryContract
    {
        $this->getQuery()->where('app_id', $app->getId());

        return $this;
    }

    /**
     * Limiting accounts to those having given uuid.
     * 
     * @param string $uuid
     * @return AccountQueryContract
     */
    public function whereUuid(string $uuid): AccountQueryContract
    {
        $this->getQuery()->where('uuid', $uuid);

        return $this;
    }

    /**
     * Limiting accounts to those considered as active.
     * 
     * @return AccountQueryContract
     */
    public function active(): AccountQueryContract
    {
        $this->getQuery()->whereNotNull('uuid');

        return $this;
    }

    /**
     * Limiting accounts to those considered as inactive.
     * 
     * @return AccountQueryContract
     */
    public function inactive(): AccountQueryContract
    {
        $this->getQuery()->whereNull('uuid');

        return $this;
    }

    /**
     * Limiting accounts to given professional only.
     * 
     * @param ProfessionalContract $professional Professional to limit for.
     * @return AccountQueryContract
     */
    public function whereProfessional(ProfessionalContract $professional): AccountQueryContract
    {
        $this->getQuery()->where('professional_id', $professional->getId());

        return $this;
    }

    /**
     * Limiting accounts to those accessed at least at specified date.
     * 
     * @param Carbon $accessed_at_least_at.
     * @return AccountQueryContract
     */
    public function accessedAtLeastAt(Carbon $accessed_at_least_at): AccountQueryContract
    {
        $this->getQuery()->whereHas('accountAccessEntries', function(Builder $query) use ($accessed_at_least_at) {
            $query->accessedAtLeastAt($accessed_at_least_at);
        });

        return $this;
    }

    /**
     * Limiting accounts to those having last access strictly before specified date.
     * 
     * @param Carbon $accessed_at_least_at.
     * @return AccountQueryContract
     */
    public function lastAccessBefore(Carbon $accessed_before): AccountQueryContract
    {
        $this->getQuery()->whereHas('accountAccessEntries', function(Builder $query) use ($accessed_before) {
            $query->accessedBefore($accessed_before)
                ->lastAccessEntryByAccount();
        });

        return $this;
    }

    /**
     * Limiting accounts to those not having last access.
     * 
     * @return AccountQueryContract
     */
    public function notAccessed(): AccountQueryContract
    {
        $this->getQuery()->where(function($query) {
            $query->doesntHave('lastAccountAccessEntry');
        });

        return $this;
    }

    /**
     * Limiting accounts to those not having last access or having access before given date.
     * 
     * @param Carbon $accessed_at_least_at.
     * @return AccountQueryContract
     */
    public function notAccessedOrLastAccessBefore(Carbon $accessed_before): AccountQueryContract
    {
        $this->getQuery()->where(function($query) use ($accessed_before) {
            $query->lastAccessBefore($accessed_before)
                ->orWhere(function($query) { 
                    $query->notAccessed();
                 });
        });
        
        return $this;
    }

    /**
     * Limiting accounts to those not concerning dashboard.
     * 
     * @return AccountQueryContract
     */
    public function notDashboard(): AccountQueryContract
    {
        $this->getQuery()->whereHas('app', function($query) {
            $query->notDashboard();
        });
        
        return $this;
    }

    /**
     * Limiting accounts to those having trial status.
     * 
     * @return AccountQueryContract
     */
    public function havingTrialStatus(): AccountQueryContract
    {
        $this->getQuery()->whereHas('chargebee', function(Builder $query) {
            $this->getAccountChargebeeQuery()
                ->setQuery($query)
                ->inTrial();
        });

        return $this;
    }

    /**
     * Limiting accounts to those having active status.
     * 
     * @return AccountQueryContract
     */
    public function havingActiveStatus(): AccountQueryContract
    {
        $this->getQuery()->whereHas('chargebee', function(Builder $query) {
            $this->getAccountChargebeeQuery()
                ->setQuery($query)
                ->active();
        });
        
        return $this;
    }

    /**
     * Limiting accounts to those having cancelled status.
     * 
     * @return AccountQueryContract
     */
    public function havingCancelledStatus(): AccountQueryContract
    {
        $this->getQuery()->whereHas('chargebee', function(Builder $query) {
            $this->getAccountChargebeeQuery()
                ->setQuery($query)
                ->cancelled();
        });

        return $this;
    }

    /**
     * Limiting accounts to those having cancelled status.
     * 
     * @return AccountQueryContract
     */
    public function havingNonRenewingStatus(): AccountQueryContract
    {
        $this->getQuery()->whereHas('chargebee', function(Builder $query) {
            $this->getAccountChargebeeQuery()
                ->setQuery($query)
                ->nonRenewing();
        });

        return $this;
    }

    /**
     * Limiting accounts to those having cancelled status.
     * 
     * @param string $status account status key
     * @return AccountQueryContract
     */
    public function havingStatus(string $status): AccountQueryContract
    {
        $this->getQuery()->whereHas('chargebee', function(Builder $query) use ($status) {
            $this->getAccountChargebeeQuery()
                ->setQuery($query)
                ->whereStatus($status);
        });

        return $this;
    }
}
