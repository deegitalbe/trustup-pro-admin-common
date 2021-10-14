<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Carbon\Carbon;
use Jenssegers\Mongodb\Relations\BelongsTo;
use Jenssegers\Mongodb\Relations\EmbedsOne;
use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\MongoModel;
use Deegitalbe\TrustupProAdminCommon\Models\AccountAccessEntryUser;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryUserContract;

class AccountAccessEntry extends MongoModel implements AccountAccessEntryContract
{
    protected $fillable = ['access_at'];

    protected $dates = ['access_at'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Package::account());
    }

    public function user(): EmbedsOne
    {
        return $this->embedsOne(Package::accountAccessEntryUser());
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
        $this->user()->save($user);

        return $this;
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
}