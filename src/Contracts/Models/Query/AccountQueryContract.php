<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query;

use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Builder;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Henrotaym\LaravelModelQueries\Queries\Contracts\QueryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AccountChargebeeQueryContract;

/**
 * Query used to retrieve accounts.
 */
interface AccountQueryContract extends QueryContract
{
    /**
     * Limiting accounts to those linked to given app.
     * 
     * @param AppContract $app
     * @return AccountQueryContract
     */
    public function whereApp(AppContract $app): AccountQueryContract;

    /**
     * Limiting accounts to those having given uuid.
     * 
     * @param string $uuid
     * @return AccountQueryContract
     */
    public function whereUuid(string $uuid): AccountQueryContract;

    /**
     * Limiting accounts to those considered as active.
     * 
     * @return AccountQueryContract
     */
    public function active(): AccountQueryContract;

    /**
     * Limiting accounts to given professional only.
     * 
     * @param ProfessionalContract $professional Professional to limit for.
     * @return AccountQueryContract
     */
    public function whereProfessional(ProfessionalContract $professional): AccountQueryContract;

    /**
     * Limiting accounts to those accessed at least at specified date.
     * 
     * @param Carbon $accessed_at_least_at.
     * @return AccountQueryContract
     */
    public function accessedAtLeastAt(Carbon $accessed_at_least_at): AccountQueryContract;

    /**
     * Limiting accounts to those having last access strictly before specified date.
     * 
     * @param Carbon $accessed_at_least_at.
     * @return AccountQueryContract
     */
    public function lastAccessBefore(Carbon $accessed_before): AccountQueryContract;

    /**
     * Limiting accounts to those not having last access.
     * 
     * @return AccountQueryContract
     */
    public function notAccessed(): AccountQueryContract;

    /**
     * Limiting accounts to those not having last access or having access before given date.
     * 
     * @param Carbon $accessed_at_least_at.
     * @return AccountQueryContract
     */
    public function notAccessedOrLastAccessBefore(Carbon $accessed_before): AccountQueryContract;

    /**
     * Limiting accounts to those not concerning dashboard.
     * 
     * @return AccountQueryContract
     */
    public function notDashboard(): AccountQueryContract;

    /**
     * Limiting accounts to those having trial status.
     * 
     * @return AccountQueryContract
     */
    public function havingTrialStatus(): AccountQueryContract;

    /**
     * Limiting accounts to those having active status.
     * 
     * @return AccountQueryContract
     */
    public function havingActiveStatus(): AccountQueryContract;

    /**
     * Limiting accounts to those having cancelled status.
     * 
     * @return AccountQueryContract
     */
    public function havingCancelledStatus(): AccountQueryContract;

    /**
     * Limiting accounts to those having cancelled status.
     * 
     * @return AccountQueryContract
     */
    public function havingNonRenewingStatus(): AccountQueryContract;

    /**
     * Limiting accounts to those having cancelled status.
     * 
     * @param string $status account status key
     * @return AccountQueryContract
     */
    public function havingStatus(string $status): AccountQueryContract;
}
