<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Exception;
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
    
    /**
     * Telling if status is concerning non premium account.
     * 
     * @return bool
     */
    public function isNonPremium(): bool;

    public function isCancelled(): bool;

    public function isPaused(): bool;
    
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
     * Setting related price.
     * 
     * @param int $price
     * @return static
     */
    public function setPrice(int $price): AccountChargebeeContract;

    /**
     * Getting related price.
     * 
     * @return int
     */
    public function getPrice(): int;

    /**
     * Get related price in euro.
     * 
     * @return float
     */
    public function getPriceInEuro(): float;

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
     * Merging attributes from given model instance.
     * 
     * This does not persist data.
     * 
     * @param AccountChargebeeContract $accountChargebee
     * @return AccountChargebeeContract
     */
    public function mergeAttributesFromModel(AccountChargebeeContract $accountChargebee): AccountChargebeeContract;

    /**
     * Refreshing its own attributes from chargebee api directly.
     * 
     * This does persist data.
     * 
     * @param bool $force Forcing update in app database.
     * @return AccountChargebeeContract
     */
    public function refreshFromApi(bool $force = false): AccountChargebeeContract;

    /**
     * Refreshing its own attributes from given subscription.
     * 
     * This does persist data.
     * 
     * @param SubscriptionContract $subscription
     * @param bool $force Forcing update in app database.
     * @return AccountChargebeeContract
     */
    public function refreshFromSubscription(SubscriptionContract $subscription, bool $force = false): AccountChargebeeContract;

    /**
     * Getting chargebee subscription.
     * 
     * @return SubscriptionContract|null
     */
    public function getChargebeeSubscription(): ?SubscriptionContract;

    /**
     * Telling if this account chargebee is different than stored one concerning app database.
     * 
     * This method is used to determine if a webhook should be sent to the application to update its accounts.
     * 
     * @return bool
     */
    public function shouldBeUpdatedInApp(): bool;

    /**
     * Telling if this account chargebee is different than given one concerning app database.
     * 
     * @param AccountChargebeeContract $chargebee
     * @return bool
     */
    public function isDifferentConcerningAppDatabase(AccountChargebeeContract $chargebee): bool;

    /**
     * Setting attributes based on given subscription.
     * 
     * MUST BE LINKED TO ACCOUNT !
     * 
     * @param SubscriptionContract $subscription
     * @return AccountChargebeeContract
     * @throws Exception If not linked to account.
     */
    public function fromSubscription(SubscriptionContract $subscription): AccountChargebeeContract;

    /**
     * Linking chargebee status to given account.
     * 
     * @param AccountContract $account
     * @return AccountChargebeeContract
     */
    public function setAccount(AccountContract $account): AccountChargebeeContract;
    
    /**
     * Telling if this status is about to be paused.
     * 
     * It's depending on pause alert threshold.
     * 
     * @return bool
     */
    public function isCloseToBePaused(): bool;

    /**
     * Telling if this subscription is paused due to unpaid invoices.
     * 
     * @return bool
     */
    public function isPausedDueToUnpaidInvoices(): bool;

    /**
     * Setting pause reason.
     * 
     * @param string|null $reason
     * @return static
     */
    public function setPauseReason(?string $reason): AccountChargebeeContract;

    /**
     * Setting pause reason to be unpaid invoices.
     * 
     * @return static
     */
    public function setUnpaidInvoicesAsPauseReason(): AccountChargebeeContract;

     /**
     * Getting pause reason.
     * 
     * @return string|null
     */
    public function getPauseReason(): ?string;

     /**
     * Telling if professional should be warned about pause.
     * 
     * It's depending on pause alert threshold.
     * 
     * @return bool
     */
    public function shouldAlertAboutPause(): bool;

    /**
     * Setting pause alert threshold.
     * 
     * @param int $days
     * @return static
     */
    public function setPauseAlertThreshold(int $days): AccountChargebeeContract;

    /**
     * Setting pause threshold.
     * 
     * @return static
     */
    public function setDefaultPauseAlertThreshold(): AccountChargebeeContract;
    
    /**
     * Getting pause alert threshold.
     * 
     * @return int
     */
    public function getPauseAlertThreshold(): int;

    /**
     * Telling if this status should be paused as soon as possible.
     * 
     * It's depending on cancel threshold.
     * 
     * @return bool
     */
    public function shouldBePaused(): bool;

    /**
     * Telling if this status should be resumed as soon as possible.
     * 
     * It's depending on pause_reason.
     * 
     * @return bool
     */
    public function shouldBeResumed(): bool;

    /**
     * Setting pause threshold.
     * 
     * @param int $days
     * @return static
     */
    public function setPauseThreshold(int $days): AccountChargebeeContract;

    /**
     * Setting pause threshold.
     * 
     * @return static
     */
    public function setDefaultPauseThreshold(): AccountChargebeeContract;

    /**
     * Getting pause threshold.
     * 
     * @return int
     */
    public function getPauseThreshold(): int;

    /**
     * Getting expected pause date.
     * 
     * @return Carbon|null
     */
    public function getExpectedPauseAt(): ?Carbon;

    /**
     * Getting days before expected pause.
     * 
     * @return int|null
     */
    public function getDaysBeforeExpectedPause(): ?int;

    /**
     * Getting last unpaid invoice due date.
     * 
     * @return Carbon|null
     */
    public function getFirstUnpaidInvoiceAt(): ?Carbon;

    /**
     * Setting last unpaid invoice.
     * 
     * @param Carbon|null $invoice
     * @return static
     */
    public function setFirstUnpaidInvoiceAt(?Carbon $dueDate): AccountChargebeeContract;

    /**
     * Telling if linked to an unpaid invoice.
     * 
     * @return bool
     */
    public function havingLastUnpaidInvoiceAt(): bool;

    /**
     * Telling if switch to annual billing is possible.
     * 
     * @return bool
     */
    public function isAnnualBillingSwitchPossible(): bool;
}