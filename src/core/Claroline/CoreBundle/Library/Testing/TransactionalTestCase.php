<?php

namespace Claroline\CoreBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class TransactionalTestCase extends WebTestCase
{
    /** @var Claroline\CoreBundle\Library\Testing\TransactionalTestClient */
    protected $client;

    protected function setUp()
    {
        parent::setUp();
        $this->client = self::createClient();
        $this->client->beginTransaction();
    }

    protected function tearDown()
    {
        $this->client->shutdown();
        parent::tearDown();
    }
}