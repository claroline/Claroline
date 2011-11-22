<?php

namespace Claroline\CommonBundle\Library\Testing;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TransactionalTestCase extends WebTestCase
{
    /** @var Claroline\CommonBundle\Service\Testing\TransactionalTestClient */
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