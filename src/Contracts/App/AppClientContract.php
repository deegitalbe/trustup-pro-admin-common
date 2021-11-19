<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\App;

use stdClass;
use Illuminate\Support\Collection;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AccountContract;

/**
 * API client able to communicate with application
 * 
 */
interface AppClientContract
{
    /**
     * Updating application account.
     * 
     * @param AccountContract $account
     * @return bool success status.
     */
    public function updateAccount(AccountContract $account): ?stdClass;

    /**
     * Getting raw professional accounts.
     * 
     * @param Professional $professional
     * @return Collection|null
     */
    public function getProfessionalAccounts($professional): ?Collection;

    /**
     * Getting raw professional account matching given uuid.
     * 
     * @param Professional $professional
     * @return stdClass|null
     */
    public function getProfessionalAccount($professional, string $account_uuid): ?stdClass;

    /**
     * Getting all accounts.
     * 
     * @return Collection|null
     */
    public function getAllAccounts(): ?Collection;

    /**
     * Getting app linked to this client.
     * 
     * @return AppContract
     */
    public function getApp(): AppContract;

    /**
     * Creating client for given application.
     * 
     * @param AppContract $app
     */
    public static function forApp(AppContract $app): AppClientContract;

    /**
     * Linking app to client.
     * 
     * @param AppContract $app
     * @return self
     */
    public function setApp(AppContract $app);
}