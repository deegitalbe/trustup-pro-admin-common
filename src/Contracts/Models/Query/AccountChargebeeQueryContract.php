<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query;

use Henrotaym\LaravelModelQueries\Queries\Contracts\QueryContract;

/**
 * Query used to retrieve account chargebee statuses.
 */
interface AccountChargebeeQueryContract extends QueryContract
{
    /**
     * Limiting chargebee status to given status key.
     * 
     * @param string $key
     * @return AccountChargebeeQueryContract
     */
    public function whereStatus(string $key): AccountChargebeeQueryContract;

    /**
     * Limiting chargebee statuses to those having given id.
     *  
     * @param string $subscription_id
     * @return AccountChargebeeQueryContract
     */
    public function whereId(string $subscription_id): AccountChargebeeQueryContract;

    /**
     * Limiting chargebee status to in trial.
     * 
     * @return AccountChargebeeQueryContract
     */
    public function inTrial(): AccountChargebeeQueryContract;

    /**
     * Limiting chargebee status to active.
     * 
     * @return AccountChargebeeQueryContract
     */
    public function active(): AccountChargebeeQueryContract;

    /**
     * Limiting chargebee status to non renewing.
     * 
     * @return AccountChargebeeQueryContract
     */
    public function nonRenewing(): AccountChargebeeQueryContract;

    /**
     * Limiting chargebee status to cancelled.
     * 
     * @return AccountChargebeeQueryContract
     */
    public function cancelled(): AccountChargebeeQueryContract;
}