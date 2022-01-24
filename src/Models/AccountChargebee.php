<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Relations\BelongsTo;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\PersistableMongoModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\PlanQueryContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
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
        'subscription_id',
        'trial_ending_at',
        'is_chargeable'
    ];

    protected $dates = [
        'trial_ending_at'
    ];

    protected $casts = [
        'is_chargeable' => 'boolean'
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Package::account());
    }

    /**
     * Linked plan relation.
     * 
     * @return BelongsTo
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Package::plan());
    }

    /**
     * Linking chargebee status to given account.
     * 
     * @param AccountContract $account
     * @return AccountChargebeeContract
     */
    public function setAccount(AccountContract $account): AccountChargebeeContract
    {
        $account->setChargebee($this);
        
        return $this;
    }

    /**
     * Getting ending trial date.
     * 
     * @return Carbon|null
     */
    public function getTrialEndingAt(): ?Carbon
    {
        return $this->trial_ending_at;
    }

    /**
     * Setting ending trial date.
     * 
     * @param Carbon|null $ending_at
     * @return AccountChargebeeContract
     */
    public function setTrialEndingAt(?Carbon $ending_at): AccountChargebeeContract
    {
        $this->trial_ending_at = $ending_at;

        return $this;
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

    /**
     * Setting account status chargeability.
     * 
     * @param bool $is_chargeable
     * @return AccountChargebeeContract
     */
    public function setIsChargeable(bool $is_chargeable): AccountChargebeeContract
    {
        $this->is_chargeable = $is_chargeable;

        return $this;
    }

    /**
     * Getting account status chargeability.
     * 
     * @return bool
     */
    public function getIsChargeable(): bool
    {
        return !!$this->is_chargeable;
    }


    /**
     * Refreshing its own attributes from chargebee api directly.
     * 
     * This does persist data.
     * 
     * @return AccountChargebeeContract
     */
    public function refreshFromApi(): AccountChargebeeContract
    {
        $subscription = app()->make(SubscriptionApiContract::class)->find($this->getId());
        
        if (!$subscription):
            return $this;
        endif;

        return $this->refreshFromSubscription($subscription);
    }

    /**
     * Refreshing its own attributes from given subscription.
     * 
     * This does persist data.
     * 
     * @param SubscriptionContract $subscription
     * @return AccountChargebeeContract
     */
    public function refreshFromSubscription(SubscriptionContract $subscription): AccountChargebeeContract
    {
        $this->fromSubscription($subscription);

        // Updating app database if needed.
        if ($this->shouldBeUpdatedInApp()):
            $this->getAccount()
                    ->updateInApp();
        endif;
        
        return $this->persist();
    }

    /**
     * Telling if this account chargebee is different than stored one concerning app database.
     * 
     * This method is used to determine if a webhook should be sent to the application to update its accounts.
     * 
     * @return bool
     */
    public function shouldBeUpdatedInApp(): bool
    {
        return $this->isDifferentConcerningAppDatabase($this->fresh());
    }

    /**
     * Telling if this account chargebee is different than given one concerning app database.
     * 
     * @param AccountChargebeeContract $chargebee
     * @return bool
     */
    public function isDifferentConcerningAppDatabase(AccountChargebeeContract $chargebee): bool
    {
        return $this->getId() !== $chargebee->getId()
            || $this->getStatus() !== $chargebee->getStatus();
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
     * Getting linked plan.
     * 
     * @return PlanContract|null
     */
    public function getPlan(): ?PlanContract
    {
        return $this->plan;
    }

    /**
     * Telling if account chargebee has a plan.
     * 
     * @return bool
     */
    public function hasPlan(): bool
    {
        return !!$this->plan;
    }

    /**
     * Setting linked plan.
     * 
     * @param PlanContract|null $plan
     * @return AccountChargebeeContract
     */
    public function setPlan(?PlanContract $plan): AccountChargebeeContract
    {
        $this->plan()->associate(optional($plan)->persist());

        return $this;
    }

    /**
     * Setting attributes based on given subscription.
     * 
     * @param SubscriptionContract $subscription
     * @return AccountChargebeeContract
     */
    public function fromSubscription(SubscriptionContract $subscription): AccountChargebeeContract
    {
        $is_chargeable = optional($subscription->getCustomer())->isChargeable() ?? false;

        $this->setStatus($subscription->getStatus())
            ->setTrialEndingAt($subscription->getTrialEndingAt())
            ->setId($subscription->getId())
            ->setIsChargeable($is_chargeable);

        $plan = app()->make(PlanQueryContract::class)
            ->whereName($subscription->getPlan()->getId())
            ->whereApp($this->getAccount()->getApp())
            ->first();

        if (!$plan):
            return $this;
        endif;

        return $this->setPlan($plan);
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