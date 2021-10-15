<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\_Abstract;

use Deegitalbe\TrustupProAdminCommon\Contracts\EmbeddableContract;
use Deegitalbe\TrustupProAdminCommon\Models\_Abstract\_Private\MongoModel;

/**
 * Model that should be embedded only.
 */
abstract class EmbeddableMongoModel extends MongoModel implements EmbeddableContract {}