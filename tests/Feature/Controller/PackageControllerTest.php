<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Feature\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Http\Controllers\PackageController;

class PackageAdminControllerTest extends TestCase
{
    /**
     * @test
     */
    public function package_admin_controller_not_writing_log_if_latest()
    {
        $request = (new Request())->merge(['version' => "0.0.1"]);
        $controller = app()->make(PackageController::class);

        Package::shouldReceive('version')
            ->withNoArgs()
            ->andReturn('2.0.0');

        $response = new TestResponse($controller->version($request));

        $response->assertJsonFragment(['is_outdated' => false]);
    }

    /**
     * @test
     */
    public function package_admin_controller_writing_log_if_outdated()
    {
        $request = (new Request())->merge(['version' => "2.0.0"]);
        $controller = app()->make(PackageController::class);
        
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function($message, $context) {
                return $message === "Package test is outdated."
                    && $context['new_version'] === "2.0.0"
                    && $context['actual_version'] === "1.0.0";
            });

        Package::shouldReceive('version')
            ->withNoArgs()
            ->andReturn('1.0.0')
            ->shouldReceive('prefix')
                ->withNoArgs()
                ->andReturn('test');

        $response = new TestResponse($controller->version($request));
        
        $response->assertJsonFragment(['is_outdated' => true]);
    }
}