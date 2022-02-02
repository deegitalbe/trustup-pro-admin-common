<?php
namespace Deegitalbe\TrustupProAdminCommon\Facades;

use Deegitalbe\TrustupProAdminCommon\Package as UnderlyingPackage;
use Henrotaym\LaravelPackageVersioning\Facades\Abstracts\VersionablePackageFacade;

class Package extends VersionablePackageFacade
{
    public static function getPackageClass(): string
    {
        return UnderlyingPackage::class;
    }
}