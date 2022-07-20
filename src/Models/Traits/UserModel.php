<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Traits;

use Deegitalbe\ChargebeeClient\Chargebee\Models\Contracts\CustomerContract;
use Deegitalbe\TrustupProAdminCommon\Models\Traits\BeingPersistable;

/**
 * Representing user for this package.
 */
trait UserModel
{
    use 
        BeingPersistable
    ;
    
    /**
     * Getting user first name.
     * 
     * @return string|null
     * 
     */
    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    /**
     * Getting user last name.
     * 
     * @return string|null
     * 
     */
    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    /**
     * Getting user email.
     * 
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Transforming user to customer.
     * 
     * @return CustomerContract|null
     */
    public function toCustomer(): ?CustomerContract
    {
        if (!$this->transformableToCustomer()):
            return null;
        endif;

        return app()->make(CustomerContract::class)
            ->setEmail($this->getEmail())
            ->setFirstName($this->getFirstName())
            ->setLastName($this->getLastName());
    }

    /**
     * Telling if this user could be transformed to customer.
     * 
     * @return bool
     */
    public function transformableToCustomer(): bool
    {
        return $this->getLastName() && $this->getFirstName() && $this->getEmail();
    }
}