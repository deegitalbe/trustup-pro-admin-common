<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\_Abstract;

use Jenssegers\Mongodb\Eloquent\Model;
use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;
use Deegitalbe\TrustupProAdminCommon\Contracts\Models\_Abstract\MongoModelContract;

abstract class MongoModel extends Model implements PersistableContract
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mongodb';

    /**
     * Persisting model in database.
     * 
     * @return static
     */
    public function persist(array $options = [])
    {
        return tap($this)->save($options);
    }
}