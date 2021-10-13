<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts;

/**
 * Representing something that can be persisted.
 */
interface PersistableContract
{
    /**
     * Persisting instance.
     * 
     * @param array $options
     */
    public function persist(array $options = []);
}