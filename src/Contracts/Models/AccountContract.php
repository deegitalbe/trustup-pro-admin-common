<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;

/**
 * Professional app account.
 * 
 */
interface AccountContract extends PersistableContract
{
    public function getChargebee(): ?AccountChargebeeContract;

    public function setChargebee(?AccountChargebeeContract $chargebee);

    /**
     * Get account access entries
     * 
     * @return Collection Collection[App\Apps\Contracts\AccountAccessEntryContract]
     */
    public function getAccountAccessEntries(): Collection;
    
    /**
     * Adding an acces entry to account.
     * 
     * @param AccountAccessEntryContract $access_entry
    */
    public function addAccountAccessEntry(AccountAccessEntryContract $access_entry);

    /**
     * Get last account access entry.
     * 
     * @return AccountAccessEntryContract|null null if not accessed yet.
     */
    public function getLastAccountAccessEntry(): ?AccountAccessEntryContract;

    /**
     * Updating application account using API.
     * 
     * @return bool success status.
     */
    public function updateInApp(): bool;

    public function getApp(): ?AppContract;

    public function setApp(AppContract $app);

    public function setProfessional($professional);

    public function getProfessional();

    public function setInitialCreatedAt($date);

    public function getInitialCreatedAt(): ?Carbon;

    /**
     * Get delete date.
     * 
     * @return Carbon|null null if not deleted.
     */
    public function getDeletedAt(): ?Carbon;

    /**
     * Get last time account was accessed.
     * 
     * @return Carbon|null null if not accessed yet.
     */
    public function getLastAccessAt(): ?Carbon;
    
    /**
     * Set delete date.
     * @param Carbon|null $deleted_at
     */
    public function setDeletedAt(?Carbon $deleted_at);

    /**
     * Get synchronization date.
     * 
     * @return Carbon|null null if not synchronized yet.
     */
    public function getSynchronizedAt(): ?Carbon;
    
    /**
     * Set synchronization date.
     * @param Carbon $synchronized_at
     */
    public function setSynchronizedAt(Carbon $synchronized_at): AccountContract;

    public function setRaw(array $data);

    public function getRaw(): ?array;

    public function getUuid(): ?string;

    public function setUuid(?string $uuid);

    public function isActive(): bool;

    public function hasChargebee(): bool;
}