<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountChargebeeContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountAccessEntryContract;

/**
 * Professional app account.
 * 
 */
interface AccountContract extends PersistableContract
{
    /**
     * Getting related chargebee status.
     * 
     * @return AccountChargebeeContract|null
     */
    public function getChargebee(): ?AccountChargebeeContract;

    /**
     * Setting related chargebee status.
     * 
     * @param AccountChargebeeContract $chargebee Status to link to.
     * @return static
     */
    public function setChargebee(?AccountChargebeeContract $chargebee): AccountContract;

    /**
     * Refreshing account status directly from chargebee API.
     * 
     * @param bool $force Forcing update in app database.
     * @return static
     */
    public function refreshChargebee(bool $force = false): AccountContract;

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
     * @return static
    */
    public function addAccountAccessEntry(AccountAccessEntryContract $access_entry): AccountContract;

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

    /**
     * Getting related app.
     * 
     * @return AppContract|null
     */
    public function getApp(): ?AppContract;

    /**
     * Setting related app.
     * 
     * @param AppContract $app App to link to.
     * @return static
     */
    public function setApp(AppContract $app): AccountContract;

    /**
     * Setting related app.
     * 
     * @param ProfessionalContract $professional Professional to link to.
     * @return static
     */
    public function setProfessional(ProfessionalContract $professional): AccountContract;

    /**
     * Getting related professional.
     * 
     * @return ProfessionalContract|null
     */
    public function getProfessional(): ?ProfessionalContract;

    /**
     * Setting related app.
     * 
     * @param Carbon|null $date Creation date.
     * @return static
     */
    public function setInitialCreatedAt(?Carbon $date): AccountContract;

    /**
     * Getting initial created_at.
     * 
     * @return Carbon|null
     */
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
     * @return static
     */
    public function setDeletedAt(?Carbon $deleted_at): AccountContract;

    /**
     * Get synchronization date.
     * 
     * @return Carbon|null null if not synchronized yet.
     */
    public function getSynchronizedAt(): ?Carbon;
    
    /**
     * Set synchronization date.
     * @param Carbon $synchronized_at
     * @return static
     */
    public function setSynchronizedAt(Carbon $synchronized_at): AccountContract;

    /**
     * Setting account as inactive one.
     * 
     * @return static
     */
    public function setAsInactive(): AccountContract;

    /**
     * Setting raw account data from app environment.
     * 
     * @param array|null $data Raw account data.
     * @return static
     */
    public function setRaw(?array $data = null): AccountContract;

    /**
     * Getting account raw data.
     * 
     * @return array|null
     */
    public function getRaw(): ?array;

    /**
     * Getting account uuid.
     * 
     * @return string|null
     */
    public function getUuid(): ?string;

    /**
     * Setting account uuid.
     * 
     * @param string|null $uuid Uuid to set.
     * @return static
     */
    public function setUuid(?string $uuid): AccountContract;

    /**
     * Telling if account can be considered as active.
     * 
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Telling if account is having a valid chargebee subscription.
     * 
     * @return bool
     */
    public function hasChargebee(): bool;
}