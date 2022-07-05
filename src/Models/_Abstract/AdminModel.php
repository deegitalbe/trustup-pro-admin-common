<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\_Abstract;

use Deegitalbe\TrustupProAdminCommon\Models\Traits\BeingPersistable;
use Illuminate\Database\Eloquent\Model;

/**
 * Do not extends this class directly!
 * You should use EmbeddableMongoModel or PersistableMongoModel
 */
abstract class AdminModel extends Model
{
    use BeingPersistable;
    
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'admin';
}