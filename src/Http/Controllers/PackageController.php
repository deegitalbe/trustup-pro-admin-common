<?php
namespace Deegitalbe\TrustupProAdminCommon\Http\Controllers;

use Illuminate\Routing\Controller;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;

class PackageController extends Controller
{
    /**
     * Getting project version.
     * 
     */
    public function version()
    {
        return response(['data' => Package::version()]);
    }
}