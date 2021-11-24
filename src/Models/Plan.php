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
     * Telling if plan is default one.
     * 
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->is_default_plan ?? false;
    }

    /**
     * Getting app linked to this plan.
     */
    public function getApp(): AppContract
    {
        return $this->app;
    }
}