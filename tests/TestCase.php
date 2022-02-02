<?php
namespace Deegitalbe\TrustupProAdminCommon\Tests;

use Deegitalbe\TrustupProAdminCommon\Tests\MongoTestDatabase;

class TestCase extends NotUsingDatabaseTestCase
{
    use
        MongoTestDatabase
    ;
}