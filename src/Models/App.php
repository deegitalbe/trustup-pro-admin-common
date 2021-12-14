<?php
namespace Deegitalbe\TrustupProAdminCommon\Models;

use Illuminate\Support\Collection;
use Jenssegers\Mongodb\Eloquent\Builder;
use Jenssegers\Mongodb\Relations\HasMany;
use Jenssegers\Mongodb\Relations\EmbedsMany;
use Deegitalbe\TrustupProAdminCommon\Models\Plan;
use Deegitalbe\TrustupProAdminCommon\App\AppClient;
use Deegitalbe\TrustupProAdminCommon\Models\Account;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\App\AppClientContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\PersistableMongoModel;

class App extends PersistableMongoModel implements AppContract
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

    public function plans(): EmbedsMany
    {
        return $this->embedsMany(Package::plan());
    }

    /**
     * Adding given plan to app plans.
     * 
     * @return AppContract
     */
    public function addPlan(PlanContract $plan): AppContract
    {
        $this->plans()->associate($plan);

        return $this;
    }

    /**
     * Removing given plan from app plans.
     * 
     * @return AppContract
     */
    public function removePlan(PlanContract $plan): AppContract
    {
        $this->plans()->dissociate($plan);

        return $this;
    }

    /**
     * Getting all accounts linked to app and given professional.
     * 
     * @param ProfessionalContract $professsional
     * @return Collection
     * 
     */
    public function getProfessionalAccounts(ProfessionalContract $professional): Collection
    {
        return $this->accounts()
            ->whereProfessional($professional)
            ->get();
    }

    /**
     * Telling if app is having at least one account matching given professional.
     * 
     * @param ProfessionalContract $professional
     * @return bool
     */
    public function isHavingProfessionalAccount(ProfessionalContract $professional): bool
    {
        return $this->accounts()->active()->whereProfessional($professional)->exists();
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
     * Getting all plans linked to app.
     * 
     * @return Collection
     * 
     */
    public function getPlans(): Collection
    {
        return $this->plans;
    }

    /**
     * Getting default plan linked to app.
     * 
     * @return PlanContract|null
     */
    public function getDefaultPlan(): ?PlanContract
    {
        return $this->getPlans()->first(function(PlanContract $plan) {
            return $plan->isDefault();
        }) ?? $this->plans->first();
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
        return $this->url . (config('app.env') !== 'production' ? ".test" : "");
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
     * Scope limiting application to those not matching given key.
     * 
     * @param Builder $query
     * @param string $key
     * @return Builder
     * 
     */
    public function scopeWhereKeyIsNot(Builder $query, string $key): Builder
    {
        return $query->where("key", '!=', $key);
    }

    /**
     * Scope limiting application to those not being dashboard.
     * 
     * @param Builder $query
     * @param string $key
     * @return Builder
     * 
     */
    public function scopeNotDashboard(Builder $query): Builder
    {
        return $query->whereKeyIsNot(self::DASHBOARD);
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

    /**
     * Scope limiting to not available applications only.
     * 
     * @param Builder $query
     * @return Builder
     * 
     */
    public function scopeWhereNotAvailable(Builder $query): Builder
    {
        return $query->where("available", false);
    }

    /**
     * Scope limiting application to those having given professional account.
     * 
     * @param Builder $query
     * @param ProfessionalContract $professional
     * @return Builder
     */
    public function scopeHavingProfessionalAccount(Builder $query, ProfessionalContract $professional): Builder
    {
        return $query->whereHas('accounts', function ($query) use ($professional) {
            return $query->active()->whereProfessional($professional);
        });
    }

}