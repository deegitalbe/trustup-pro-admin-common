<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts;

/**
 * Representing something that has a context.
 */
interface ContextualContract
{
    /**
     * Getting context.
     * 
     * @return array
     */
    public function context(): array;
}