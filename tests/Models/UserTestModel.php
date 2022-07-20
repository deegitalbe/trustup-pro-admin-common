<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Models;

use Deegitalbe\TrustupProAdminCommon\Models\Traits\ProfessionalModel;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\UserContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\AdminModel;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\PersistableMongoModel;
use Deegitalbe\TrustupProAdminCommon\Models\Traits\UserModel;

/**
 * Representing professional.
 */
class UserTestModel extends AdminModel implements UserContract
{
    use UserModel;

    protected $fillable = ['first_name', 'last_name', 'email'];
}