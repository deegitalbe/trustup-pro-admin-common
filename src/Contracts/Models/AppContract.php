<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Illuminate\Support\Collection;
use Henrotaym\LaravelApiClient\Contracts\ClientContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\App\AppClientContract;

interface AppContract extends PersistableContract
{
    /**
     * Application client
     * 
     * @return AppClientContract
     */
    public function getClient(): AppClientContract;

    /**
     * Getting all accounts linked to app and given professional.
     * 
     * @param Professional $professsional
     * @return Collection
     * 
     */
    public function getProfessionalAccounts($professional): Collection;

    /**
     * Getting all accounts linked to app.
     * 
     * @return Collection
     * 
     */
    public function getAccounts(): Collection;

    /**
     * Getting application id (database purpose)
     * 
     * @return bool
     * 
     */
    public function getId(): ?string;

    /**
     * Telling if application is using a billing plan.
     * 
     * @return bool
     * 
     */
    public function getPaid(): bool;

    /**
     * Setting if application should use a billing plan.
     * 
     * @param bool $paid
     * 
     */
    public function setPaid(bool $paid);
    
    /**
     * Application name.
     * 
     * @return string
     * 
     */
    public function getName(): string;

    /**
     * Setting Application name.
     * 
     * @param string
     * 
     */
    public function setName(string $name);

    /**
     * Application key identifier.
     * 
     * @return string
     * 
     */
    public function getKey(): string;

    /**
     * Setting Application key identifier.
     * 
     * @param string
     * 
     */
    public function setKey(string $key);

    /**
     * Application url.
     * 
     * @return string
     * 
     */
    public function getUrl(): string;

    /**
     * Setting Application url.
     * 
     * @param string
     * 
     */
    public function setUrl(string $url);

    /**
     * Application description.
     * 
     * @return string
     * 
     */
    public function getDescription(): string;

    /**
     * Setting Application description.
     * 
     * @param string
     * 
     */
    public function setDescription(string $description);

    /**
     * Application availability.
     * 
     * @return bool
     * 
     */
    public function getAvailable(): bool;

    /**
     * Setting Application availability.
     * 
     * @param bool
     * 
     */
    public function setAvailable(bool $is_available);

    /**
     * Telling if application is translated.
     * 
     * @return bool
     * 
     */
    public function getTranslated(): bool;

    /**
     * Setting if app is translated or not.
     * 
     * @param bool
     * 
     */
    public function setTranslated(bool $is_translated);

    /**
     * Telling if application is dashboard app.
     * 
     * @return bool
     * 
     */
    public function isDashboard(): bool;

    /**
     * Application order (used to display app in a predefined order).
     * 
     * @return int
     * 
     */
    public function getOrder(): int;

    /**
     * Setting Application order.
     * 
     * @param string
     * 
     */
    public function setOrder(int $order);

    /**
     * Setting application as next one (last order).
     * 
     * @param string
     * 
     */
    public function setAsNextApplication();

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = []);
}