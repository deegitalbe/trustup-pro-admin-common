<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Traits;

use Carbon\Carbon;

/**
 * Trait implementing professional model contract.
 */
trait ProfessionalModel
{
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
     * @return string
     */
    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    /**
     * Persisting instance.
     * @param array $options
     * @return self
     */
    public function persist(array $options = []): self
    {
        $this->save($options);

        return $this;
    }
}