<?php

namespace Claroline\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Claroline\CommonBundle\Library\Testing\TransactionalTestCase;

class PlatformIndexControllerTest extends TransactionalTestCase
{
        
    public function testControllerRendersDefaultTemplateIfNoIndexApplicationIsSet()
    {
        $crawler = $this->client->request('GET', '/');
        $defaultDiv = $crawler->filter('#content #claro_default_index_content');
        
        $this->assertEquals(1, $defaultDiv->count());
    }
}