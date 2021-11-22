<?php
namespace Deegitalbe\TrustupProAdminCommon\Facades;

use Illuminate\Support\Facades\Facade;
use Deegitalbe\TrustupProAdminCommon\Package as UnderlyingPackage;

class Package extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return UnderlyingPackage::$prefix;
    }
}