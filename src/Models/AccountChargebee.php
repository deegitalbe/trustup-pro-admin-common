<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\AdminModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\Query\PlanQueryContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionInvoiceApiContract;

class AccountChargebee extends AdminModel implements AccountChargebeeContract
{
    /** @var SubscriptionContract|null */
    protected $chargebeeSubscription;

    /** @var bool */
    protected $chargebeeSubscriptionRetrieved = false;

    /**
     * Status trial key.
     * 
     * @var string
     */
    const TRIAL = "in_trial";

    /**
     * Non premium status key.
     * 
     * @var string
     */
    const NON_PREMIUM = "non_premium";

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

    /**
     * Status paused key.
     * 
     * @var string
     */
    const PAUSED = "paused";

    protected $fillable = [
        'status',
        'subscription_id',
        'plan_id',
        'trial_ending_at',
        'is_chargeable',
        'pause_alert_threshold',
        'pause_threshold',
        'pause_reason',
        'price'
    ];

    protected $dates = [
        'trial_ending_at',
        'first_unpaid_invoice_at'
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

        if ( $this->isNonPremium() ) {
            return "Non premium";
        }
        
        if ( $this->isCancelled() ) {
            return "Annulé";
        }

        if ( $this->isPaused() ) {
            return "En pause";
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
     * Merging attributes from given model instance.
     * 
     * This does not persist data.
     * 
     * @param AccountChargebeeContract $accountChargebee
     * @return AccountChargebeeContract
     */
    public function mergeAttributesFromModel(AccountChargebeeContract $accountChargebee): AccountChargebeeContract
    {
        $conditionalAttributes = collect(['pause_threshold', 'pause_alert_threshold']);
        
        // Not keeping conditional attributes without values.
        $attributes = collect($accountChargebee->getAttributes())
            ->reject(fn ($attributeValue, $attributeName) => !$attributeValue && $conditionalAttributes->contains($attributeName))
            ->all();

        return $this->fill($attributes);
    }

    /**
     * Getting chargebee subscription from API directly.
     * 
     * @return SubscriptionContract|null
     */
    protected function getFreshChargebeeSubscription(): ?SubscriptionContract
    {
        if (!$this->getId()):
            return null;
        endif;

        /** @var SubscriptionApiContract */
        $api = app()->make(SubscriptionApiContract::class);

        return $api->find($this->getId());
    }

    /**
     * Getting chargebee subscription.
     * 
     * @return SubscriptionContract|null
     */
    public function getChargebeeSubscription(): ?SubscriptionContract
    {
        if ($this->chargebeeSubscriptionRetrieved):
            return $this->chargebeeSubscription;
        endif;

        $this->chargebeeSubscriptionRetrieved = true;

        return $this->chargebeeSubscription = $this->getFreshChargebeeSubscription();
    }

    /**
     * Refreshing its own attributes from chargebee api directly.
     * 
     * This does persist data.
     * 
     * @param bool $force Forcing update in app database.
     * @return AccountChargebeeContract
     */
    public function refreshFromApi(bool $force = false): AccountChargebeeContract
    {
        if (!$this->getChargebeeSubscription()):
            return $this;
        endif;

        $this->refreshFromSubscription($this->getChargebeeSubscription(), $force);

        /** @var SubscriptionInvoiceApiContract */
        $invoiceApi = app()->make(SubscriptionInvoiceApiContract::class);
        $invoice = $invoiceApi->setSubscription($this->getChargebeeSubscription())->firstLate();

        return $this->setFirstUnpaidInvoiceAt(optional($invoice)->getDueDate())->persist();
    }

    /**
     * Refreshing its own attributes from given subscription.
     * 
     * This does persist data.
     * 
     * @param SubscriptionContract $subscription
     * @param bool $force Forcing update in app database.
     * @return AccountChargebeeContract
     */
    public function refreshFromSubscription(SubscriptionContract $subscription, bool $force = false): AccountChargebeeContract
    {
        $this->fromSubscription($subscription);

        // Updating app database if needed.
        if ($force || $this->shouldBeUpdatedInApp()):
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

    /**
     * Telling if status is concerning non premium account.
     * 
     * @return bool
     */
    public function isNonPremium(): bool
    {
        return $this->status === self::NON_PREMIUM;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::CANCELLED;
    }

    public function isNonRenewing(): bool
    {
        return $this->status === self::NON_RENEWING;
    }

    public function isPaused(): bool
    {
        return $this->status === self::PAUSED;
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
     * Setting related price.
     * 
     * @param int $price
     * @return static
     */
    public function setPrice(int $price): AccountChargebeeContract
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Getting related price.
     * 
     * @return int|null
     */
    public function getPrice(): ?int
    {
        return $this->price;
    }

    /**
     * Getting related price in euro.
     * 
     * @return float|null
     */
    public function getPriceInEuro(): ?float
    {
        return $this->price ?
            $this->price / 100
            : null;
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
     * MUST BE LINKED TO ACCOUNT !
     * 
     * @param SubscriptionContract $subscription
     * @return AccountChargebeeContract
     * @throws Exception If not linked to account.
     */
    public function fromSubscription(SubscriptionContract $subscription): AccountChargebeeContract
    {
        $is_chargeable = optional($subscription->getCustomer())->isChargeable() ?? false;

        $this->setStatus($subscription->getStatus())
            ->setTrialEndingAt($subscription->getTrialEndingAt())
            ->setId($subscription->getId())
            ->setIsChargeable($is_chargeable)
            ->setPrice(optional($subscription->getPlan())->getPriceInCent());

        if (!$this->isPaused()):
            $this->setPauseReason(null);
        endif;

        $plan = app()->make(PlanQueryContract::class)
            ->whereName($subscription->getPlan()->getId())
            ->whereAppOrGlobal($this->getAccount()->getApp())
            ->first();

        return $this->setPlan($plan);
    }

    /**
     * Telling if this subscription is paused due to unpaid invoices.
     * 
     * @return bool
     */
    public function isPausedDueToUnpaidInvoices(): bool
    {
        return $this->isPaused() && $this->pause_reason === "unpaid_invoices";
    }

    /**
     * Setting pause reason.
     * 
     * @param string|null $reason
     * @return static
     */
    public function setPauseReason(?string $reason): AccountChargebeeContract
    {
        $this->pause_reason = $reason;

        return $this;
    }

    /**
     * Setting pause reason to be unpaid invoices.
     * 
     * @return static
     */
    public function setUnpaidInvoicesAsPauseReason(): AccountChargebeeContract
    {
        return $this->setPauseReason("unpaid_invoices");
    }

    /**
     * Getting pause reason.
     * 
     * @return string|null
     */
    public function getPauseReason(): ?string
    {
        return $this->pause_reason;
    }

    /**
     * Telling if this status is about to be paused.
     * 
     * It's depending on cancel alert threshold.
     * 
     * @return bool
     */
    public function isCloseToBePaused(): bool
    {
        if (!$this->havingLastUnpaidInvoiceAt() || !$this->isPausable()):
            return false;
        endif;

        return $this->getFirstUnpaidInvoiceAt()->addDays($this->getPauseAlertThreshold())->isBefore(now())
            && !$this->shouldBePaused();
    }

    /**
     * Telling if professional should be warned about pause.
     * 
     * It's depending on cancel alert threshold.
     * 
     * @return bool
     */
    public function shouldAlertAboutPause(): bool
    {
        if (!$this->havingLastUnpaidInvoiceAt() || !$this->isPausable()):
            return false;
        endif;

        return $this->getFirstUnpaidInvoiceAt()->addDays($this->getPauseAlertThreshold())->isSameDay();
    }


    /**
     * Setting pause alert threshold.
     * 
     * @param int $days
     * @return static
     */
    public function setPauseAlertThreshold(int $days): AccountChargebeeContract
    {
        $this->pause_alert_threshold = $days;

        return $this;
    }

    /**
     * Setting pause threshold.
     * 
     * @return static
     */
    public function setDefaultPauseAlertThreshold(): AccountChargebeeContract
    {
        return $this->setPauseAlertThreshold(9);
    }
    
    /**
     * Getting pause alert threshold.
     * 
     * @return int
     */
    public function getPauseAlertThreshold(): int
    {
        if (!$this->pause_alert_threshold):
            $this->setDefaultPauseAlertThreshold()->save();
        endif;
        
        return $this->pause_alert_threshold;
    }

    /**
     * Telling if this status should be paused as soon as possible.
     * 
     * It's depending on pause threshold.
     * 
     * @return bool
     */
    public function shouldBePaused(): bool
    {
        if (!$this->havingLastUnpaidInvoiceAt() || !$this->isPausable()):
            return false;
        endif;

        return $this->getFirstUnpaidInvoiceAt()->addDays($this->getPauseThreshold())->isBefore(now());
    }

    /**
     * Telling if this status should be resumed as soon as possible.
     * 
     * It's depending on pause_reason.
     * 
     * @return bool
     */
    public function shouldBeResumed(): bool
    {
        if (!$this->isPausedDueToUnpaidInvoices()):
            return false;
        endif;

        if (!$this->havingLastUnpaidInvoiceAt()):
            return true;
        endif;

        return !$this->getFirstUnpaidInvoiceAt()->addDays($this->getPauseThreshold())->isBefore(now());
    }

    /**
     * Setting pause threshold.
     * 
     * @param int $days
     * @return static
     */
    public function setPauseThreshold(int $days): AccountChargebeeContract
    {
        $this->pause_threshold = $days;

        return $this;
    }

    /**
     * Setting pause threshold.
     * 
     * @return static
     */
    public function setDefaultPauseThreshold(): AccountChargebeeContract
    {
        return $this->setPauseThreshold(30);
    }

    /**
     * Getting pause threshold.
     * 
     * @return int
     */
    public function getPauseThreshold(): int
    {
        if (!$this->pause_threshold):
            $this->setDefaultPauseThreshold()->save();
        endif;
        
        return $this->pause_threshold;
    }

    /**
     * Getting expected paused date.
     * 
     * @return Carbon|null
     */
    public function getExpectedPauseAt(): ?Carbon
    {
        if (!$this->havingLastUnpaidInvoiceAt() || !$this->isPausable()):
            return null;
        endif;

        return $this->getFirstUnpaidInvoiceAt()->addDays($this->getPauseThreshold());
    }

    /**
     * Telling if included in pausable statuses.
     * 
     * @return bool
     */
    protected function isPausable(): bool
    {
        return $this->isActive() || $this->isNonRenewing();
    }

    /**
     * Getting days before expected pause.
     * 
     * @return int|null
     */
    public function getDaysBeforeExpectedPause(): ?int
    {
        if (!$cancellationAt = $this->getExpectedPauseAt()):
            return null;
        endif;

        if (!$diff = $cancellationAt->diffInDays()):
            return $this->shouldBePaused() ? 0 : 1;
        endif;

        return $diff;
    }

    /**
     * Getting last unpaid invoice due date.
     * 
     * @return Carbon|null
     */
    public function getFirstUnpaidInvoiceAt(): ?Carbon
    {
        return $this->first_unpaid_invoice_at;
    }

    /**
     * Setting last unpaid invoice.
     * 
     * @param Carbon|null $invoice
     * @return static
     */
    public function setFirstUnpaidInvoiceAt(?Carbon $dueDate): AccountChargebeeContract
    {
        $this->first_unpaid_invoice_at = $dueDate;

        return $this;
    }

    /**
     * Telling if switch to annual billing is possible.
     * 
     * @return bool
     */
    public function isAnnualBillingSwitchPossible(): bool
    {
        if (!$this->hasPlan()
            || $this->getPlan()->isYearlyBilled()
            || $this->isTrial() && !$this->getIsChargeable()
        ):
            return false;
        endif;

        return true;
    }

    /**
     * Telling if linked to an unpaid invoice.
     * 
     * @return bool
     */
    public function havingLastUnpaidInvoiceAt(): bool
    {
        return !!$this->getFirstUnpaidInvoiceAt();
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

    /**
     * Limiting chargebee status to freemium.
     * 
     * @param Builder $query
     * @return Builder
     */
    public function scopeActiveFreemium(Builder $query): Builder
    {
        return $query->whereStatus(self::NON_PREMIUM);
    }
}