<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit\Facades;

use Deegitalbe\TrustupProAdminCommon\Facades\Package;
use Deegitalbe\TrustupProAdminCommon\Tests\NotUsingDatabaseTestCase;
use Deegitalbe\TrustupVersionedPackage\Contracts\Project\ProjectContract;

class PackageTest extends NotUsingDatabaseTestCase {
    /**
     * @test
     */
    public function package_facade_getting_defined_projects_only()
    {
        config([Package::getPrefix().'.projects' => ['https://localhost', null]]);
        $projects = Package::getProjects();

        $this->assertCount(1, $projects);
        $this->assertInstanceOf(ProjectContract::class, $projects->first());
        $this->assertEquals('https://localhost', $projects->first()->getUrl());
    }
}