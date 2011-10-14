<?php

namespace Claroline\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PlatformIndexControllerTest extends WebTestCase
{
    private $client;
    
    public function setUp()
    {
        $this->client = self::createClient();
    }
    
    public function testControllerRendersDefaultTemplateIfNoIndexApplicationIsSet()
    {
        $crawler = $this->client->request('GET', '/');
        $defaultDiv = $crawler->filter('#content #claro_default_index_content');
        
        $this->assertEquals(1, $defaultDiv->count());
    }
}