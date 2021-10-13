<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Relations\HasMany;
use Deegitalbe\TrustupProAdminCommon\App\AppClient;
use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\MongoModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\App\AppClientContract;

class App extends MongoModel implements AppContract
{
    /**
     * Dashboard key (trustup.pro)
     * 
     */
    const DASHBOARD = 'dashboard';
    
    /**
     * Facturation key (facturation.trustup.pro)
     * 
     */
    const INVOICING = 'invoicing';
    
    /**
     * Agenda key (agenda.trustup.pro)
     * 
     */
    const AGENDA = 'agenda';
    
    /**
     * Tasks key (taches.trustup.pro)
     * 
     */
    const TASKS = 'tasks';
    
    /**
     * Construction key (construction.trustup.pro?)
     * 
     */
    const CONSTRUCTION = 'construction';
    
    /**
     * Timetracker key (timetracker.trustup.pro)
     * 
     */
    const TIMETRACKER = 'timetracker';

    protected $fillable = [
        'key',
        'url',
        'name',
        'description',
        'available',
        'translated',
        'paid',
        'order'
    ];

    protected $casts = [
        'available' => "boolean",
        'translated' => "boolean",
        'paid' => "boolean"
    ];
    
    /**
     * Accounts relation.
     * 
     * @return HasMany
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Package::account());
    }

    /**
     * Getting all accounts linked to app and given professional.
     * 
     * @param Professional $professsional
     * @return Collection
     * 
     */
    public function getProfessionalAccounts($professional): Collection
    {
        return $this->accounts()
            ->whereProfessional($professional)
            ->get();
    }

    /**
     * Getting all accounts linked to app.
     * 
     * @return Collection
     * 
     */
    public function getAccounts(): Collection
    {
        return $this->accounts()->get();
    }

    /**
     * Getting application id (database purpose).
     * 
     * @return bool
     * 
     */
    public function getId(): ?string
    {
        return $this->id;
    }
    
    /**
     * Telling if application is using a billing plan.
     * 
     * @return bool
     * 
     */
    public function getPaid(): bool
    {
        return $this->paid;
    }

    /**
     * Setting if application should use a billing plan.
     * 
     * @param bool $paid
     * 
     */
    public function setPaid(bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }
    
    /**
     * Application name.
     * 
     * @return string
     * 
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Setting Application name.
     * 
     * @param string
     * 
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Application key identifier.
     * 
     * @return string
     * 
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Setting Application key identifier.
     * 
     * @param string
     * 
     */
    public function setKey(string $key): self
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Application url.
     * 
     * @return string
     * 
     */
    public function getUrl(): string
    {
        return $this->url . (config('app.env') === 'local' ? ".test" : "");
    }

    /**
     * Setting Application url.
     * 
     * @param string
     * 
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Application description.
     * 
     * @return string
     * 
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Setting Application description.
     * 
     * @param string
     * 
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Application availability.
     * 
     * @return bool
     * 
     */
    public function getAvailable(): bool
    {
        return $this->available;
    }

    /**
     * Setting Application availability.
     * 
     * @param bool
     * 
     */
    public function setAvailable(bool $is_available): self
    {
        $this->available = $is_available;

        return $this;
    }

    /**
     * Telling if application is translated.
     * 
     * @return bool
     * 
     */
    public function getTranslated(): bool
    {
        return $this->translated;
    }

    /**
     * Setting if app is translated or not.
     * 
     * @param bool
     * 
     */
    public function setTranslated(bool $is_translated): self
    {
        $this->translated = $is_translated;

        return $this;
    }

    /**
     * Telling if application is my trustup app.
     * 
     * @return bool
     * 
     */
    public function isDashboard(): bool
    {
        return $this->getKey() === self::DASHBOARD;
    }

    /**
     * Application order (used to display app in a predefined order).
     * 
     * @return int
     * 
     */
    public function getOrder(): int
    {
        return $this->order;
    }

    /**
     * Setting Application order.
     * 
     * @param string
     * 
     */
    public function setOrder(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Setting application as next one (last order).
     * 
     * @param string
     * 
     */
    public function setAsNextApplication(): self
    {
        $this->order = self::count() + 1;

        return $this;
    }

    /**
     * Save the model to the database.
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        if (!$this->order):
            $this->setAsNextApplication();
        endif;

        return parent::save($options);
    }

    /**
     * Application client
     * 
     * @return AppClientContract
     */
    public function getClient(): AppClientContract
    {
        return AppClient::forApp($this);
    }

    /**
     * Scope limiting application to given key.
     * 
     * @param Builder $query
     * @param string $key
     * @return Builder
     * 
     */
    public function scopeWhereAppKey(Builder $query, string $key): Builder
    {
        return $query->where("key", $key);
    }

    /**
     * Scope ordering applications by order attribute.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeByOrder(Builder $query): Builder
    {
        return $query->orderBy("order", "ASC");
    }

    /**
     * Scope limiting to available applications only.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeWhereAvailable(Builder $query): Builder
    {
        return $query->where("available", true);
    }

}