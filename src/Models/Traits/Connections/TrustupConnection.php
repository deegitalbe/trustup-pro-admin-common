<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Traits\Connections;

use Deegitalbe\TrustupProAdminCommon\Facades\Package;

/**
 * Setting up model connection to match trustup DB connection.
 * 
 */
trait TrustupConnection
{
    public function initializeTrustupConnection()
    {
        $this->setConnection(Package::trustupConnection());
    }
}