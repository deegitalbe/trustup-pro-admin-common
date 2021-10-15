<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\_Abstract\_Private;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * Do not extends this class directly!
 * You should use EmbeddableMongoModel or PersistableMongoModel
 */
abstract class MongoModel extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';
}