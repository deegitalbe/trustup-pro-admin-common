<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Carbon\Carbon;
use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Illuminate\Contracts\Support\Arrayable;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;

/**
 * Representing professional model for this package.
 */
interface ProfessionalContract extends PersistableContract, Arrayable
{
    /**
     * Getting professional id.
     * 
     * @return int
     * 
     */
    public function getId(): int;

    /**
     * Getting professional authorization key.
     * 
     * @return string
     * 
     */
    public function getAuthorizationKey(): string;

    /**
     * Getting professional creation date.
     * 
     * @return Carbon
     */
    public function getCreatedAt(): Carbon;

    /**
     * Getting customer id.
     * 
     * @return string|null
     */
    public function getCustomerId(): ?string;

    /**
     * Getting customer.
     * 
     * @return CustomerContract|null
     */
    public function getCustomer(): ?CustomerContract;

    /**
     * Setting customer.
     * 
     * @param CustomerContract|null
     * @return ProfessionalContract
     */
    public function setCustomer(?CustomerContract $customer): ProfessionalContract;
}