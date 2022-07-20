<?php
namespace Deegitalbe\TrustupProAdminCommon\Models\Traits\Connections;

use Deegitalbe\TrustupProAdminCommon\Facades\Package;

/**
 * Setting up model connection to match admin DB connection.
 * 
 */
trait AdminConnection
{
    public function initializeAdminConnection()
    {
        $this->setConnection(Package::adminConnection());
    }
}