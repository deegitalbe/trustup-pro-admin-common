<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;

interface AccountChargebeeContract extends PersistableContract
{
    public function getStatus(): string;

    public function setStatus(string $status);

    public function getId(): ?string;

    public function setId(string $id);

    public function text(): string;

    public function isTrial(): bool;

    public function isActive(): bool;

    public function isCancelled(): bool;
    
    public function isNonRenewing(): bool;

    public function getAccount(): AccountContract;

    /**
     * Getting linked plan.
     * 
     * @return PlanContract|null
     */
    public function getPlan(): ?PlanContract;

    /**
     * Setting linked plan.
     * 
     * @param PlanContract|null $plan
     * @return AccountChargebeeContract
     */
    public function setPlan(?PlanContract $plan): AccountChargebeeContract;

    /**
     * Getting ending trial date.
     * 
     * @return Carbon|null
     */
    public function getTrialEndingAt(): ?Carbon;

    /**
     * Setting ending trial date.
     * 
     * @param Carbon|null $ending_at
     * @return AccountChargebeeContract
     */
    public function setTrialEndingAt(?Carbon $ending_at): AccountChargebeeContract;

    /**
     * Setting account status chargeability.
     * 
     * @param bool $is_chargeable
     * @return AccountChargebeeContract
     */
    public function setIsChargeable(bool $is_chargeable): AccountChargebeeContract;

    /**
     * Getting account status chargeability.
     * 
     * @return bool
     */
    public function getIsChargeable(): bool;

    /**
     * Telling if account chargebee has a plan.
     * 
     * @return bool
     */
    public function hasPlan(): bool;

    /**
     * Refreshing its own attributes from chargebee api directly.
     * 
     * This does persist data.
     * 
     * @return AccountChargebeeContract
     */
    public function refreshFromApi(): AccountChargebeeContract;

    /**
     * Setting attributes based on given subscription.
     * 
     * @param SubscriptionContract $subscription
     * @return AccountChargebeeContract
     */
    public function fromSubscription(SubscriptionContract $subscription): AccountChargebeeContract;

    /**
     * Linking chargebee status to given account.
     * 
     * @param AccountContract $account
     * @return AccountChargebeeContract
     */
    public function setAccount(AccountContract $account): AccountChargebeeContract;
}