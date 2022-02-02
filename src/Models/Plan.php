<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Jenssegers\Mongodb\Relations\HasMany;
use Jenssegers\Mongodb\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Collection;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\PersistableMongoModel;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionPlanApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;

/**
 * Representing app plan.
 */
class Plan extends PersistableMongoModel implements PlanContract
{
    /**
     * Fillable attributes.
     * 
     * @var array
     */
    protected $fillable = [
        'name',
        'trial_duration',
        'is_default_plan',
        'is_default_yearly_plan',
        'price'
    ];
    
    /**
     * Casting attributes.
     * 
     * @var array
     */
    protected $casts = [
        'is_default_plan' => "boolean",
        'is_default_yearly_plan' => "boolean"
    ];

    /**
     * Linked app.
     * 
     * @return BelongsTo
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(Package::app());
    }

    /**
     * Linked account chargebees relation.
     * 
     * @return HasMany
     */
    public function accountChargebees(): HasMany
    {
        return $this->HasMany(Package::accountChargebee());
    }

    /**
     * Setting plan name.
     * 
     * @param string
     * @return PlanContract
     */
    public function setName(string $name): PlanContract
    {
        $this->name = $name;

        return $this;
    }
    
    /**
     * Setting plan trial duration in days.
     * 
     * @param string
     * @return PlanContract
     */
    public function setTrialDuration(int $trial_duration): PlanContract
    {
        $this->trial_duration = $trial_duration;

        return $this;
    }

    /**
     * Setting if plan is default.
     * 
     * @param bool $is_default
     * @return PlanContract
     */
    public function setIsDefault(bool $is_default): PlanContract
    {
        $this->is_default_plan = $is_default;

        return $this;
    }

    /**
     * Setting plan price (in cent) from cent value given.
     * 
     * @param int $cent_price
     * @return PlanContract
     */
    public function setPriceInCent(int $cent_price): PlanContract
    {
        $this->price = $cent_price;

        return $this;
    }
    
    /**
     * Setting plan price (in cent) from euro value given.
     * 
     * @param float $euro_price
     * @return PlanContract
     */
    public function setPriceInEuro(float $euro_price): PlanContract
    {
        $this->price = bcmul($euro_price, 100, 0);

        return $this;
    }
    
    /**
     * Getting plan id.
     * 
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Getting plan duration in days.
     * 
     * @return int
     */
    public function getTrialDuration(): int
    {
        return $this->trial_duration;
    }

    /** 
     * Telling if plan is having a trial period.
     * 
     * @return bool
     */
    public function hasTrialDuration(): bool
    {
        return $this->getTrialDuration() > 0;
    }

    /**
     * Telling if plan is default one.
     * 
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->is_default_plan ?? false;
    }

    /**
     * Getting plan price in cent.
     * 
     * @return int
     */
    public function getPriceInCent(): int
    {
        return $this->price;
    }
    
    /**
     * Getting plan price in euro.
     * 
     * @return float
     */
    public function getPriceInEuro(): float
    {
        return bcdiv($this->price, 100, 2);
    }

    /**
     * Getting app linked to this plan.
     */
    public function getApp(): AppContract
    {
        return $this->app;
    }

    /**
     * Getting linked account chargebees.
     * 
     * @return Collection
     */
    public function getAccountChargebees(): Collection
    {
        return $this->accountChargebees;
    }

    /**
     * Refreshing its own attributes from chargebee api directly.
     * 
     * This does persist data.
     * 
     * @return PlanContract
     */
    public function refreshFromApi(): PlanContract
    {
        $subscription_plan = app()->make(SubscriptionPlanApiContract::class)->find($this->getName());

        if (!$subscription_plan) {
            return $this;
        }

        $this->fromSubscriptionPlan($subscription_plan)
            ->persist();

        return $this;
    }

    /**
     * Setting attributes based on given subscription plan.
     * 
     * @param SubscriptionPlanContract $subscription
     * @return PlanContract
     */
    public function fromSubscriptionPlan(SubscriptionPlanContract $subscription_plan): PlanContract
    {
        $this->setName($subscription_plan->getId())
            ->setPriceInCent($subscription_plan->getPriceInCent())
            ->setTrialDuration($subscription_plan->getTrialDuration());

        return $this;
    }

    /**
     * Transform plan to subscription plan.
     * 
     * @return SubscriptionPlanContract
     */
    public function toSubscriptionPlan(): SubscriptionPlanContract
    {
        return app()->make(SubscriptionPlanContract::class)
            ->setId($this->getName())
            ->setTrialDuration($this->getTrialDuration())
            ->setPriceInCent($this->getPriceInCent());
    }
}