<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Relations\BelongsTo;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\PersistableMongoModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;

class AccountChargebee extends PersistableMongoModel implements AccountChargebeeContract
{
    /**
     * Status trial key.
     * 
     * @var string
     */
    const TRIAL = "in_trial";

    /**
     * Status active key.
     * 
     * @var string
     */
    const ACTIVE = "active";

    /**
     * Status cancel key.
     * 
     * @var string
     */
    const CANCELLED = "cancelled";

    /**
     * Status non renewing key.
     * 
     * @var string
     */
    const NON_RENEWING = "non_renewing";

    protected $fillable = [
        'status',
        'subscription_id'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Package::account());
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function text(): string
    {
        if ( $this->isTrial() ) {
            return "Essai";
        }

        if ( $this->isActive() ) {
            return "Actif";
        }
        
        if ( $this->isCancelled() ) {
            return "Annulé";
        }

        if ( $this->isNonRenewing() ) {
            return "Terminé";
        }

        return "";
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getId(): ?string
    {
        return $this->subscription_id;
    }

    public function setId(string $id)
    {
        $this->subscription_id = $id;

        return $this;
    }

    public function isTrial(): bool
    {
        return $this->status === self::TRIAL;
    }

    public function isActive(): bool
    {
        return $this->status === self::ACTIVE;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::CANCELLED;
    }

    public function isNonRenewing(): bool
    {
        return $this->status === self::NON_RENEWING;
    }

    public function getAccount(): AccountContract
    {
        return $this->account;
    }

    /**
     * Limiting chargebee status to given status key.
     * 
     * @param Builder $query
     * @param string $key
     * @return Builder
     */
    public function scopeWhereStatus(Builder $query, string $key): Builder
    {
        return $query->where('status', $key);
    }

    /**
     * Limiting chargebee status to in trial.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeInTrial(Builder $query): Builder
    {
        return $query->whereStatus(self::TRIAL);
    }

    /**
     * Limiting chargebee status to active.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereStatus(self::ACTIVE);
    }

    /**
     * Limiting chargebee status to non renewing.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeNonRenewing(Builder $query): Builder
    {
        return $query->whereStatus(self::NON_RENEWING);
    }

    /**
     * Limiting chargebee status to cancelled.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->whereStatus(self::CANCELLED);
    }
}