<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Traits;

use Carbon\Carbon;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\CustomerApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Contracts\SubscriptionApiContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\SubscriptionContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;

/**
 * Trait implementing professional model contract.
 */
trait ProfessionalModel
{
    protected ?SubscriptionContract $packSubscription;

    use 
        BeingPersistable
    ;

    /**
     * Telling if chargebee customer was retrieved from api.
     * 
     * @var bool
     */
    protected $chargebeeCustomerRetrieved = false;

    /**
     * Related chargebee customer.
     * 
     * @var CustomerContract|null
     */
    protected $chargebeeCustomer;

    /**
     * Getting professional id.
     * 
     * @return int
     * 
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Getting professional authorization key.
     * 
     * @return string
     * 
     */
    public function getAuthorizationKey(): string
    {
        return $this->authorization_key;
    }

    /**
     * Getting professional authorization key.
     * 
     * @return Carbon|null
     */
    public function getCreatedAt(): ?Carbon
    {
        return $this->created_at;
    }

    /**
     * Getting professional vat number.
     * 
     * @return string|null
     * 
     */
    public function getVatNumber(): ?string
    {
        return $this->vat_number;
    }

    /**
     * Getting professional company name.
     * 
     * @return string|null
     * 
     */
    public function getCompanyName(): ?string
    {
        return $this->company;
    }

    /**
     * Getting customer id.
     * 
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return $this->chargebee_customer_id;
    }

    /**
     * Getting related pack subscription id.
     */
    public function getPackSubscriptionId(): ?string
    {
        return $this->chargebee_subscription_pro_pack_id;
    }

    /**
     * Telling if having pack subscription.
     */
    public function hasPackSubscription(): bool
    {
        return !!$this->getPackSubscriptionId();
    }

    /**
     * Getting related pack subscription.
     * 
     * @return ?SubscriptionContract
     */
    public function getPackSubscription(): ?SubscriptionContract
    {
        if (isset($this->packSubscription)) return $this->packSubscription;

        /** @var SubscriptionApiContract */
        $subscriptionApi = app()->make(SubscriptionApiContract::class);

        return $this->packSubscription = $this->hasPackSubscription() ?
            $subscriptionApi->find($this->getPackSubscriptionId())
            : null;
    }

    /**
     * Getting customer.
     * 
     * @param bool $fresh
     * @return CustomerContract|null
     */
    public function getCustomer($fresh = false): ?CustomerContract
    {
        if ($this->chargebeeCustomerRetrieved && !$fresh):
            return $this->chargebeeCustomer;
        endif;

        $this->chargebeeCustomerRetrieved = true;

        return $this->chargebeeCustomer = $this->getCustomerId() ?
            app()->make(CustomerApiContract::class)->find($this->getCustomerId())
            : null;
    }

    /**
     * Setting customer.
     * 
     * @param CustomerContract|null
     * @return ProfessionalContract
     */
    public function setCustomer(?CustomerContract $customer): ProfessionalContract
    {
        $this->chargebee_customer_id = optional($customer)->getId();

        return $this;
    }

    /**
     * Setting is_active and activated_at at the same time.
     * 
     * @param bool $value
     */
    public function setIsActiveAttribute($value)
    {
        $this->attributes['is_active'] = $value;
        $this->activated_at = $value ? now() : null;
    }
}