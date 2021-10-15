<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\_Abstract;

use Deegitalbe\TrustupProAdminCommon\Contracts\PersistableContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\_Private\MongoModel;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\EmbeddableMongoModel;

/**
 * Model that can be persisted to its own collection.
 */
abstract class PersistableMongoModel extends MongoModel implements PersistableContract
{
    /**
     * Persisting model in database.
     * 
     * @return static
     */
    public function persist(array $options = [])
    {
        return tap($this)->save($options);
    }

    /**
     * Embedding given model to given relation
     * This method is needed in case we forgot that embedded relations can't be persisted.
     * 
     * @param MongoModel|null $model
     * @param string $relation
     * @return self
     */
    public function embedsOneThis(?EmbeddableMongoModel $model, string $relation): self
    {
        // If no model given, dissociate actual one.
        if (!$model) {
            $this->{$relation}()->dissociate();
            return $this->persist();
        }

        // If model exists delete it first and replace it by a now one not yet persisted.
        // It avoids data duplication.
        // This should not happen if we respect the embeddable convention but we could forget it 🤷‍♂️
        if ($model->exists):
            $class = get_class(tap($model)->delete());
            $model = (new $class)->fill($model->toArray());
        endif;

        // Associate model.
        $this->{$relation}()->associate($model);
        return $this->persist();
    }
}