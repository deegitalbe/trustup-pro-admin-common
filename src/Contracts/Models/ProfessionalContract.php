<?php
namespace Deegitalbe\TrustupProAdminCommon\Contracts\Models;

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
}