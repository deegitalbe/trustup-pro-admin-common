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
     * Telling if this status is about to be cancelled.
     * 
     * It's depending on cancel alert threshold.
     * 
     * @return bool
     */
    public function isCloseToBeCancelled(): bool;

     /**
     * Telling if professional should be warned about cancellation.
     * 
     * It's depending on cancel alert threshold.
     * 
     * @return bool
     */
    public function shouldAlertAboutCancellation(): bool;

    /**
     * Setting cancel alert threshold.
     * 
     * @param int $days
     * @return static
     */
    public function setCancelAlertThreshold(int $days): AccountChargebeeContract;

    /**
     * Setting cancel threshold.
     * 
     * @return static
     */
    public function setDefaultCancelAlertThreshold(): AccountChargebeeContract;
    
    /**
     * Getting cancel alert threshold.
     * 
     * @return int
     */
    public function getCancelAlertThreshold(): int;

    /**
     * Telling if this status should be cancelled as soon as possible.
     * 
     * It's depending on cancel threshold.
     * 
     * @return bool
     */
    public function shouldBeCancelled(): bool;

    /**
     * Setting cancel threshold.
     * 
     * @param int $days
     * @return static
     */
    public function setCancelThreshold(int $days): AccountChargebeeContract;

    /**
     * Setting cancel threshold.
     * 
     * @return static
     */
    public function setDefaultCancelThreshold(): AccountChargebeeContract;

    /**
     * Getting cancel threshold.
     * 
     * @return int
     */
    public function getCancelThreshold(): int;

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
}