<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit\Facades;

use Deegitalbe\TrustupProAdminCommon\Tests\TestCase;
use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupVersionedPackage\Contracts\Project\ProjectContract;
use Deegitalbe\TrustupVersionedPackage\Contracts\VersionedPackageCheckerContract;

class PackageTest extends TestCase {
    /**
     * @test
     */
    public function package_facade_getting_defined_projects_only()
    {
        config([Package::prefix().'.projects' => ['https://localhost', null]]);
        $projects = Package::getProjects();

        $this->assertCount(1, $projects);
        $this->assertInstanceOf(ProjectContract::class, $projects->first());
        $this->assertEquals('https://localhost', $projects->first()->getUrl());
    }
}