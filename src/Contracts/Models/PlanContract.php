<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Deegitalbe\TrustupProAdminCommon\Contracts\EmbeddableContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;

/**
 * Representing app plan.
 */
interface PlanContract extends EmbeddableContract
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
     * Getting plan name.
     * 
     * @return string
     */
    public function getName(): string;
    
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
     * Getting app linked to this plan.
     */
    public function getApp(): AppContract;
}