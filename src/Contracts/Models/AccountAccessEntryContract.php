<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Carbon\Carbon;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryUserContract;

/**
 * Account access history entry.
 * 
 */
interface AccountAccessEntryContract extends PersistableContract
{
    /**
     * Account Access date.
     * 
     * @return Carbon
     */
    public function getAccessAt(): Carbon;

    /**
     * Setting acccount access date.
     * 
     * @param Carbon $access_at
     */
    public function setAccessAt(Carbon $access_at);

    /**
     * Getting account associated with this entry.
     * 
     *  @return AccountContract
     */
    public function getAccount(): AccountContract;
    
    /**
     * Setting account linked to this entry.
     * 
     * @param AccountContract $account
     */
    public function setAccount(AccountContract $account);

    /**
     * Setting user linked to this entry.
     * 
     * @param AccountAccessEntryUserContract $user
     */
    public function setUser(AccountAccessEntryUserContract $user);

    /**
     * Getting user associated with this entry.
     * 
     *  @return AccountAccessEntryUserContract
     */
    public function getUser(): AccountAccessEntryUserContract;
}