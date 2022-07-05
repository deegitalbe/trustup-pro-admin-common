<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Relations\HasOne;
use Jenssegers\Mongodb\Relations\HasMany;
use Jenssegers\Mongodb\Relations\EmbedsOne;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Deegitalbe\TrustupProAdminCommon\Models\AccountChargebee;
use Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntry;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\PersistableMongoModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\AdminModel;

/**
 * Professional app account.
 * 
 */
class Account extends PersistableMongoModel implements AccountContract
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'professional_id',
        'app_id',
        'deleted_at',
        'synchronized_at',
        'initial_created_at',
        'raw'
    ];

    protected $dates = ['deleted_at', 'synchronized_at', 'initial_created_at'];

    protected $casts = ['raw' => 'array'];

    /**
     * App relation.
     * 
     * @return BelongsTo
     */
    public function app(): BelongsTo
    {
        return $this->belongsTo(Package::app());
    }

    public function professional(): BelongsTo
    {
        return $this->belongsTo(Package::professional());
    }

    public function accountAccessEntries(): HasMany
    {
        return $this->hasMany(Package::accountAccessEntry());
    }

    public function lastAccountAccessEntry(): HasOne
    {
        return $this->hasOne(Package::accountAccessEntry())->latest();
    }

    public function chargebee(): HasOne
    {
        return $this->hasOne(Package::accountChargebee())->latest();
    }

    /**
     * Getting related chargebee status.
     * 
     * @return AccountChargebeeContract|null
     */
    public function getChargebee(): ?AccountChargebeeContract
    {
        return $this->chargebee;
    }

    /**
     * Setting related chargebee status.
     * 
     * @param AccountChargebeeContract $chargebee Status to link to.
     * @return static
     */
    public function setChargebee(?AccountChargebeeContract $chargebee): AccountContract
    {
        // Deleting status from database
        $this->chargebee()->delete();
        
        if ( $chargebee ) {
            // Saving relationship
            $this->chargebee()->save($chargebee);
        }
        
        return $this->refresh();
    }

    /**
     * Refreshing account status directly from chargebee API.
     * 
     * @param bool $force Forcing update in app database.
     * @return static
     */
    public function refreshChargebee(bool $force = false): AccountContract
    {
        if (!$this->hasChargebee()):
            return $this;
        endif;

        $this->getChargebee()->refreshFromApi($force);

        return $this->refresh();
    }

    /**
     * Get account access entries
     * 
     * @return Collection Collection[App\Apps\Contracts\AccountAccessEntryContract]
     */
    public function getAccountAccessEntries(): Collection
    {
        return $this->accountAccessEntries;
    }

    /**
     * Adding an acces entry to account.
     * 
     * @param AccountAccessEntryContract $access_entry
     * @return static
    */
    public function addAccountAccessEntry(AccountAccessEntryContract $access_entry): AccountContract
    {
        $this->accountAccessEntries()->save($access_entry);

        return $this;
    }

    /**
     * Updating application account using API.
     * 
     * @return bool success status.
     */
    public function updateInApp(): bool
    {
        if (!$this->getApp()):
            return false;
        endif;

        $raw_account = $this->getApp()->getClient()
            ->updateAccount($this);

        return !!$raw_account;
    }

    /**
     * Setting account uuid.
     * 
     * @param string|null $uuid Uuid to set.
     * @return static
     */
    public function setUuid(?string $uuid): AccountContract
    {
        $this->uuid = $uuid;

        return $this->persist();
    }

    /**
     * Setting account as active one.
     * 
     * @return static
     */
    public function setAsInactive(): AccountContract
    {
        return $this->setUuid(null);
    }

    /**
     * Getting account uuid.
     * 
     * @return string|null
     */
    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * Getting related app.
     * 
     * @return AppContract|null
     */
    public function getApp(): ?AppContract
    {
        return $this->app;
    }

    /**
     * Setting related app.
     * 
     * @param AppContract $app App to link to.
     * @return static
     */
    public function setApp(AppContract $app): AccountContract
    {
        $this->app()->associate($app);

        return $this->persist();
    }

    /**
     * Getting related professional.
     * 
     * @return ProfessionalContract|null
     */
    public function getProfessional(): ?ProfessionalContract
    {
        return $this->professional;
    }

    /**
     * Setting related app.
     * 
     * @param ProfessionalContract $professional Professional to link to.
     * @return static
     */
    public function setProfessional(ProfessionalContract $professional): AccountContract
    {
        $this->professional()->associate($professional);

        return $this->persist();
    }

    /**
     * Getting initial created_at.
     * 
     * @return Carbon|null
     */
    public function getInitialCreatedAt(): ?Carbon
    {
        return $this->initial_created_at ? new Carbon($this->initial_created_at) : null;
    }

    /**
     * Setting related app.
     * 
     * @param Carbon|null $professional Professional to link to.
     * @return static
     */
    public function setInitialCreatedAt(?Carbon $date): AccountContract
    {
        $this->initial_created_at = $date;

        return $this->persist();
    }

    /**
     * Getting account raw data.
     * 
     * @return array|null
     */
    public function getRaw(): ?array
    {
        return $this->raw;
    }

    /**
     * Setting raw account data from app environment.
     * 
     * @param array|null $data Raw account data.
     * @return static
     */
    public function setRaw(?array $data = null): AccountContract
    {
        $this->raw = $data;

        return $this->persist();
    }

    /**
     * Get delete date.
     * 
     * @return Carbon|null null if not deleted.
     */
    public function getDeletedAt(): ?Carbon
    {
        return $this->deleted_at;
    }
    
    /**
     * Set delete date.
     * @param Carbon|null $deleted_at
     * @return static
     */
    public function setDeletedAt(?Carbon $deleted_at): AccountContract
    {
        if ($deleted_at):
            return tap($this)->delete();
        endif;
        
        $this->deleted_at = null;
        return $this->persist();
    }

    /**
     * Get synchronization date.
     * 
     * @return Carbon|null null if not synchronized yet.
     */
    public function getSynchronizedAt(): ?Carbon
    {
        return $this->synchronized_at;
    }
    
    /**
     * Set synchronization date.
     * @param Carbon $synchronized_at
     * @return static
     */
    public function setSynchronizedAt(Carbon $synchronized_at): AccountContract
    {
        $this->synchronized_at = $synchronized_at;

        return $this->persist();
    }

    /**
     * Get last time account was accessed.
     * 
     * @return Carbon|null null if not accessed yet.
     */
    public function getLastAccessAt(): ?Carbon
    {
        return optional($this->lastAccountAccessEntry)->getAccessAt();
    }

    /**
     * Get last account access entry.
     * 
     * @return AccountAccessEntryContract|null null if not accessed yet.
     */
    public function getLastAccountAccessEntry(): ?AccountAccessEntryContract
    {
        return $this->lastAccountAccessEntry;
    }

    /**
     * Telling if account can be considered as active.
     * 
     * @return bool
     */
    public function isActive(): bool
    {
        return !!$this->uuid;
    }

    /**
     * Telling if account is having a valid chargebee subscription.
     * 
     * @return bool
     */
    public function hasChargebee(): bool
    {
        return !!optional($this->getChargebee())->getId();
    }

    public function scopeWhereApp(Builder $query, AppContract $app): Builder
    {
        return $query->where('app_id', $app->getId());
    }

    public function scopeWhereUuid(Builder $query, string $uuid): Builder
    {
        return $query->where('uuid', $uuid);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('uuid');
    }

    /**
     * Scope limiting accounts to given professional only.
     * 
     * @param Builder $query
     * @param ProfessionalContract $professional Professional to limit for.
     * @return Builder
     * 
     */
    public function scopeWhereProfessional(Builder $query, ProfessionalContract $professional): Builder
    {
        return $query->where('professional_id', $professional->getId());
    }

    /**
     * Scope limiting accounts to those accessed at least at specified date.
     * 
     * @param Builder $query
     * @param Carbon $accessed_at_least_at.
     * @return Builder
     * 
     */
    public function scopeAccessedAtLeastAt(Builder $query, Carbon $accessed_at_least_at): Builder
    {
        return $query->whereHas('accountAccessEntries', function(Builder $query) use ($accessed_at_least_at) {
            $query->accessedAtLeastAt($accessed_at_least_at);
        });
    }

    /**
     * Scope limiting accounts to those having last access strictly before specified date.
     * 
     * @param Builder $query
     * @param Carbon $accessed_at_least_at.
     * @return Builder
     * 
     */
    public function scopeLastAccessBefore(Builder $query, Carbon $accessed_before): Builder
    {
        return $query->whereHas('accountAccessEntries', function(Builder $query) use ($accessed_before) {
            $query->accessedBefore($accessed_before)
                ->lastAccessEntryByAccount();
        });
    }

    /**
     * Scope limiting accounts to those not having last access.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeNotAccessed(Builder $query): Builder
    {
        return $query->where(function($query) {
            $query->doesntHave('lastAccountAccessEntry');
        });
    }

    /**
     * Scope limiting accounts to those not having last access or having access before given date.
     * 
     * @param Builder $query
     * @param Carbon $accessed_at_least_at.
     * @return Builder
     * 
     */
    public function scopeNotAccessedOrLastAccessBefore(Builder $query, Carbon $accessed_before): Builder
    {
        return $query->where(function($query) use ($accessed_before) {
            $query->lastAccessBefore($accessed_before)
                ->orWhere(function($query) { 
                    $query->notAccessed();
                 });
        });
    }

    /**
     * Scope limiting accounts to those not concerning dashboard.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeNotDashboard(Builder $query): Builder
    {
        return $query->whereHas('app', function($query) {
            $query->notDashboard();
        });
    }

    /**
     * Scope limiting accounts to those having trial status.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeHavingTrialStatus(Builder $query): Builder
    {
        return $query->whereHas('chargebee', function(Builder $query) {
            $query->inTrial();
        });
    }

    /**
     * Scope limiting accounts to those having active status.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeHavingActiveStatus(Builder $query): Builder
    {
        return $query->whereHas('chargebee', function(Builder $query) {
            $query->active();
        });
    }

    /**
     * Scope limiting accounts to those having cancelled status.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeHavingCancelledStatus(Builder $query): Builder
    {
        return $query->whereHas('chargebee', function(Builder $query) {
            $query->cancelled();
        });
    }

    /**
     * Scope limiting accounts to those having cancelled status.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeHavingNonRenewingStatus(Builder $query): Builder
    {
        return $query->whereHas('chargebee', function(Builder $query) {
            $query->nonRenewing();
        });
    }

    /**
     * Scope limiting accounts to those having cancelled status.
     * 
     * @param Builder $query
     * @param status $status account status key
     * @return Builder
     */
    public function scopeHavingStatus(Builder $query, string $status): Builder
    {
        return $query->whereHas('chargebee', function(Builder $query) use ($status) {
            $query->whereStatus($status);
        });
    }
}