<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests\Unit;

use Deegitalbe\TrustupProAdminCommon\Tests\NotUsingDatabaseTestCase;
use Henrotaym\LaravelPackageVersioning\Testing\Traits\InstallPackageTest;

class InstallVersionablePackageTest extends NotUsingDatabaseTestCase
{
    use InstallPackageTest;
}