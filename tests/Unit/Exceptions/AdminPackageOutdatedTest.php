<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit\Facades;

use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Contracts\Project\ProjectContract;
use Deegitalbe\TrustupProAdminCommon\Exceptions\Package\AdminPackageOutdated;

class AdminPackageOutdatedTest extends TestCase {
    /**
     * @test
     */
    public function admin_package_outdated_constructing_itself_with_static_get_exception()
    {
        $exception = AdminPackageOutdated::getException();
        $this->assertEquals("Package ". Package::prefix() ." is outdated.", $exception->getMessage());
    }

    /**
     * @test
     */
    public function admin_package_outdated_getting_context()
    {
        $exception = AdminPackageOutdated::getException()
            ->setNewVersion('test');

        $this->assertEquals([
            'actual_version' => Package::version(),
            'new_version' => 'test'
        ], $exception->context());
    }
}