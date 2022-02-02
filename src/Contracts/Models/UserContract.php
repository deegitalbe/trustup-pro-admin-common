<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Illuminate\Contracts\Support\Arrayable;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;

/**
 * Representing user for this package.
 */
interface UserContract extends PersistableContract, Arrayable
{
    /**
     * Getting user first name.
     * 
     * @return string|null
     * 
     */
    public function getFirstName(): ?string;

    /**
     * Getting user last name.
     * 
     * @return string|null
     * 
     */
    public function getLastName(): ?string;

    /**
     * Getting user email.
     * 
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * Transforming user to customer.
     * 
     * @return CustomerContract|null
     */
    public function toCustomer(): ?CustomerContract;

    /**
     * Telling if this user could be transformed to customer.
     * 
     * @return bool
     */
    public function transformableToCustomer(): bool;
}