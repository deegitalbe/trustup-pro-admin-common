<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\_Abstract;

use Deegitalbe\TrustupProAdminCommon\Models\Traits\BeingPersistable;
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
        CrossDatabaseRelations,
        SoftDeletes
    ;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'admin';
}