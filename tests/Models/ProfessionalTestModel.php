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
    use ProfessionalModel;
    
    protected $primaryKey = 'id';

    protected $fillable = ['authorization_key', 'id', 'created_at', 'chargebee_customer_id'];
}