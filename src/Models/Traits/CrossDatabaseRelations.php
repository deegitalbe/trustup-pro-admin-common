<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Traits;

/**
 * Setting up model for being compatible with cross database relations.
 * 
 * Keep in mind databases have to be on same host for this to work.
 */
trait CrossDatabaseRelations
{
    public function initializeCrossDatabaseRelations()
    {
        $this->setTable($this->getConnection()->getDatabaseName() . "." . $this->getTable());
    }
}