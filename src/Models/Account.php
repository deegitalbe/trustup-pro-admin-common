<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Relations\HasMany;
use Jenssegers\Mongodb\Relations\EmbedsOne;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Deegitalbe\TrustupProAdminCommon\Models\AccountChargebee;
use Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntry;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\MongoModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;

/**
 * Professional app account.
 * 
 */
class Account extends MongoModel implements AccountContract
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'professional_id',
        'app_id',
        'deleted_at'
    ];

    protected $dates = ['deleted_at'];

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

    public function chargebee(): EmbedsOne
    {
        return $this->embedsOne(Package::accountChargebee());
    }

    public function getChargebee(): ?AccountChargebeeContract
    {
        return $this->chargebee;
    }

    public function setChargebee(?AccountChargebeeContract $chargebee): self
    {
        if ( ! $chargebee ) {
            $this->chargebee()->delete();
            return $this;
        }

        $this->chargebee()->save($chargebee);
        return $this;
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
     * @return self
    */
    public function addAccountAccessEntry(AccountAccessEntryContract $access_entry): self
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

    public function setUuid(?string $uuid): self
    {
        $this->uuid = $uuid;

        return $this->persist();
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getApp(): ?AppContract
    {
        return $this->app;
    }

    public function setApp(AppContract $app): self
    {
        $this->app()->associate($app);

        return $this->persist();
    }

    public function getProfessional()
    {
        return $this->professional;
    }

    public function setProfessional($professional): self
    {
        $this->professional()->associate($professional);

        return $this->persist();
    }

    public function getInitialCreatedAt(): ?Carbon
    {
        return $this->initial_created_at ? new Carbon($this->initial_created_at) : null;
    }

    public function setInitialCreatedAt($date): self
    {
        $this->initial_created_at = $date;

        return $this->persist();
    }

    public function getRaw(): ?array
    {
        return $this->raw;
    }

    public function setRaw(array $data = null): self
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
     * 
     * @param Carbon|null $deleted_at
     * @return self
     */
    public function setDeletedAt(?Carbon $deleted_at): self
    {
        if ($deleted_at):
            return tap($this)->delete();
        endif;
        
        $this->deleted_at = null;
        return $this->persist();
    }

    public function isActive(): bool
    {
        return !!$this->uuid;
    }

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
     * @param mixed $professional Professional to limit for.
     * @return Builder
     * 
     */
    public function scopeWhereProfessional(Builder $query, $professional): Builder
    {
        return $query->where('professional_id', $professional->id);
    }

}