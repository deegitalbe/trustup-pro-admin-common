<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\_Abstract;

use Deegitalbe\TrustupProAdminCommon\Models\Traits\BeingPersistable;
use Deegitalbe\TrustupProAdminCommon\Models\Traits\Connections\AdminConnection;
use Deegitalbe\TrustupProAdminCommon\Models\Traits\CrossDatabaseRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Extends this class to get an admin database related model.
 */
abstract class AdminModel extends Model
{
    use
        BeingPersistable,
        AdminConnection,
        CrossDatabaseRelations,
        SoftDeletes
    ;
}