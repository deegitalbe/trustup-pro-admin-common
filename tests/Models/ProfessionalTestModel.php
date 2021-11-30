<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Models;

use Deegitalbe\TrustupProAdminCommon\Contracts\Models\AppContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\PlanContract;
use Deegitalbe\TrustupProAdminCommon\Models\Traits\ProfessionalModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\ProfessionalContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\PersistableMongoModel;

/**
 * Representing professional.
 */
class ProfessionalTestModel extends PersistableMongoModel implements ProfessionalContract
{
    protected $primaryKey = 'id';

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

    protected $fillable = ['authorization_key', 'id'];
}