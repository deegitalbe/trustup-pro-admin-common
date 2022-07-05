<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Traits;

/**
 * Adding persist() method to entity.
 */
trait BeingPersistable
{
    /**
     * Persisting instance.
     * 
     * @param array $options
     * @return static
     */
    public function persist(array $options = [])
    {
        return tap($this)->save($options);
    }
}