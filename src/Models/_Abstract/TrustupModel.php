<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\_Abstract;

use Deegitalbe\TrustupProAdminCommon\Models\Traits\BeingPersistable;
use Deegitalbe\TrustupProAdminCommon\Models\Traits\Connections\TrustupConnection;
use Deegitalbe\TrustupProAdminCommon\Models\Traits\CrossDatabaseRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * Extends this class to get a trustup database related model.
 */
abstract class TrustupModel extends Model
{
    use
        BeingPersistable,
        TrustupConnection,
        CrossDatabaseRelations
    ;
}