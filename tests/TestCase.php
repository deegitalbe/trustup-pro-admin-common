<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests;

use Deegitalbe\TrustupProAdminCommon\Tests\MongoTestDatabase;
use Henrotaym\LaravelTestSuite\TestSuite;

class TestCase extends NotUsingDatabaseTestCase
{
    use
        MongoTestDatabase,
        TestSuite
    ;
}