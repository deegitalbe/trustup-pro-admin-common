<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Illuminate\Database\Eloquent\Collection;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionPlanContract;

/**
 * Representing app plan.
 */
interface PlanContract extends PersistableContract
{
    /**
     * Setting plan name.
     * 
     * @param string $name
     * @return PlanContract
     */
    public function setName(string $name): PlanContract;
    
    /**
     * Setting plan trial duration in days.
     * 
     * @param int $trial_duration
     * @return PlanContract
     */
    public function setTrialDuration(int $trial_duration): PlanContract;

    /**
     * Setting if plan is default.
     * 
     * @param bool $is_default
     * @return PlanContract
     */
    public function setIsDefault(bool $is_default): PlanContract;

    /**
     * Setting plan price (in cent) from cent value given.
     * 
     * @param int $cent_price
     * @return PlanContract
     */
    public function setPriceInCent(int $cent_price): PlanContract;
    
    /**
     * Setting plan price (in cent) from euro value given.
     * 
     * @param float $euro_price
     * @return PlanContract
     */
    public function setPriceInEuro(float $euro_price): PlanContract;

    /**
     * Getting plan price in cent.
     * 
     * @return int
     */
    public function getPriceInCent(): int;
    
    /**
     * Getting plan price in euro.
     * 
     * @return float
     */
    public function getPriceInEuro(): float;
    
    /**
     * Getting plan name.
     * 
     * @return string
     */
    public function getName(): string;

    /** 
     * Telling if plan is having a trial period.
     * 
     * @return bool
     */
    public function hasTrialDuration(): bool;
    
    /**
     * Getting plan duration in days.
     * 
     * @return int
     */
    public function getTrialDuration(): int;

    /**
     * Telling if plan is default one.
     * 
     * @return bool
     */
    public function isDefault(): bool;

    /**
     * Telling if plan is billed annually.
     * 
     * @return bool
     */
    public function isYearlyBilled(): bool;

    /**
     * Telling if plan is billed monthly.
     * 
     * @return bool
     */
    public function isMonthlyBilled(): bool;

    /**
     * Getting app linked to this plan.
     */
    public function getApp(): AppContract;

    /**
     * Getting linked account chargebees.
     * 
     * @return Collection
     */
    public function getAccountChargebees(): Collection;

    /**
     * Refreshing its own attributes from chargebee api directly.
     * 
     * This does persist data.
     * 
     * @return PlanContract
     */
    public function refreshFromApi(): PlanContract;

    /**
     * Setting attributes based on given subscription plan.
     * 
     * @param SubscriptionPlanContract $subscription
     * @return PlanContract
     */
    public function fromSubscriptionPlan(SubscriptionPlanContract $subscription_plan): PlanContract;

    /**
     * Transform plan to subscription plan.
     * 
     * @return SubscriptionPlanContract
     */
    public function toSubscriptionPlan(): SubscriptionPlanContract;
}