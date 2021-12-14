<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\EmbeddableMongoModel;

/**
 * Representing app plan.
 */
class Plan extends EmbeddableMongoModel implements PlanContract
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
        'price'
    ];
    
    /**
     * Casting attributes.
     * 
     * @var array
     */
    protected $casts = [
        'is_default_plan' => "boolean"
    ];

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
}