<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Query;

use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Henrotaym\LaravelModelQueries\Queries\Abstracts\AbstractQuery;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\AccountChargebeeQueryContract;

/**
 * Query used to retrieve account chargebee statuses.
 */
class AccountChargebeeQuery extends AbstractQuery implements AccountChargebeeQueryContract
{
    /**
     * Model linked to this query.
     * 
     * @return string
     */
    public function getModel(): string
    {
        return Package::accountChargebee();
    }

    /**
     * Limiting chargebee status to given status key.
     * 
     * @param string $key
     * @return AccountChargebeeQueryContract
     */
    public function whereStatus(string $key): AccountChargebeeQueryContract
    {
        $this->getQuery()->where('status', $key);

        return $this;
    }

    /**
     * Limiting chargebee statuses to those having given id.
     * 
     * @param string $subscription_id
     * @return AccountChargebeeQueryContract
     */
    public function whereId(string $subscription_id): AccountChargebeeQueryContract
    {
        $this->getQuery()->where('subscription_id', $subscription_id);

        return $this;
    }

    /**
     * Limiting chargebee status to in trial.
     * 
     * @return AccountChargebeeQueryContract
     */
    public function inTrial(): AccountChargebeeQueryContract
    {
        return $this->whereStatus($this->getModel()::TRIAL);
    }

    /**
     * Limiting chargebee status to active.
     * 
     * @return AccountChargebeeQueryContract
     */
    public function active(): AccountChargebeeQueryContract
    {
        return $this->whereStatus($this->getModel()::ACTIVE);
    }

    /**
     * Limiting chargebee status to non renewing.
     * 
     * @return AccountChargebeeQueryContract
     */
    public function nonRenewing(): AccountChargebeeQueryContract
    {
        return $this->whereStatus($this->getModel()::NON_RENEWING);
    }

    /**
     * Limiting chargebee status to cancelled.
     * 
     * @return AccountChargebeeQueryContract
     */
    public function cancelled(): AccountChargebeeQueryContract
    {
        return $this->whereStatus($this->getModel()::CANCELLED);
    }
}
