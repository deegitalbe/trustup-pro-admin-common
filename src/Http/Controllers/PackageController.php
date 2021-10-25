<?php
namespace Deegitalbe\TrustupProAdminCommon\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Exceptions\Package\AdminPackageOutdated;

class PackageController extends Controller
{
    /**
     * Checking package version.
     * 
     */
    public function version(Request $request)
    {
        $data = $request->validate(['version' => 'required|string']);
        $version = $data['version'];
        
        // if version is outdated, write a log
        if ($version > Package::version()):
            report(
                AdminPackageOutdated::getException()
                    ->setNewVersion($version)
            );

            return response([
                'data' =>[
                    'is_outdated' => true,
                    'new_version' => $version
                ]
            ]);
        endif;

        return response(['data' => ['is_outdated' => false]]);
    }
}