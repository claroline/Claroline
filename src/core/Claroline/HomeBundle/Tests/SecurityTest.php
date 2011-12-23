<?php

namespace Claroline\HomeBundle;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityTest extends WebTestCase
{
    /** @var \Symfony\Component\HttpKernel\Client */
    private $client;
    
    public function setUp()
    {
        $this->client = self::createClient();
        $this->client->followRedirects();
    }
    
    public function testHomeSectionIsThePlatformIndexAndIsOpenedToAnonymousUsers()
    {
        $firstCrawler = $this->client->request('GET', '/');
        $secondCrawler = $this->client->request('GET', '/home');   
        
        $this->assertTrue($firstCrawler->filter('#home.section')->count() > 0);        
        $this->assertTrue($secondCrawler->filter('#home.section')->count() > 0);
    }
}