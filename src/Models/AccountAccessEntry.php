<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Relations\BelongsTo;
use Jenssegers\Mongodb\Relations\EmbedsOne;
use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntryUser;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\PersistableMongoModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryUserContract;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class AccountAccessEntry extends PersistableMongoModel implements AccountAccessEntryContract
{
    use SoftDeletes;
    
    protected $fillable = ['access_at'];

    protected $dates = ['access_at'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Package::account());
    }

    public function user(): HasOne
    {
        return $this->hasOne(Package::accountAccessEntryUser());
    }

    /**
     * Account Access date.
     * 
     * @return Carbon
     */
    public function getAccessAt(): Carbon
    {
        return $this->access_at;
    }

    /**
     * Setting acccount access date.
     * 
     * @param Carbon $access_at
     */
    public function setAccessAt(Carbon $access_at)
    {
        $this->access_at = $access_at;

        return $this;
    }

    /**
     * Getting account linked to this entry.
     * 
     * @return AccountContract
     */
    public function getAccount(): AccountContract
    {
        return $this->account;
    }

    /**
     * Setting account linked to this entry.
     * 
     * @param AccountContract $account
     * @return self
     */
    public function setAccount(AccountContract $account): self
    {
        $this->account()->associate($account);

        return $this->persist();
    }

    /**
     * Setting user linked to this entry.
     * 
     * @param AccountAccessEntryUserContract $user
     * @return self
     */
    public function setUser(AccountAccessEntryUserContract $user): self
    {
        return $this->embedsOneThis($user, 'user');
    }

    /**
     * Getting user associated with this entry.
     * 
     *  @return AccountAccessEntryUserContract
     */
    public function getUser(): AccountAccessEntryUserContract
    {
        return $this->user;
    }

    /**
     * Scope limiting accounts access entries to those accessed at least at specified date.
     * 
     * @param Builder $query
     * @param Carbon $accessed_at_least_at.
     * @return Builder
     * 
     */
    public function scopeAccessedAtLeastAt(Builder $query, Carbon $accessed_at_least_at): Builder
    {
        return $query->where('access_at', '>=', $accessed_at_least_at);
    }

    /**
     * Scope limiting accounts access entries to those accessed strictly before specified date.
     * 
     * @param Builder $query
     * @param Carbon $accessed_at_least_at.
     * @return Builder
     * 
     */
    public function scopeAccessedBefore(Builder $query, Carbon $accessed_before): Builder
    {
        return $query->where('access_at', '<', $accessed_before);
    }

    /**
     * Scope limiting account access entries to last one by account.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeLastAccessEntryByAccount(Builder $query): Builder
    {
        return $query->whereIn(
            'created_at', 
            self::groupBy('account_id')
                ->get(['created_at'])
                ->pluck('created_at')
        );
    }
}