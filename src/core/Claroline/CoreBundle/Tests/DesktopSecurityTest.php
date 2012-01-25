<?php

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\Testing\FunctionalTestCase;

class SecurityTest extends FunctionalTestCase
{
    /** @var array[User] */
    private $users;
    
    public function setUp()
    {
        parent::setUp();
        $this->users = $this->loadUserFixture();
        $this->client->followRedirects();
    }
    
    public function testDesktopSectionRequiresAuthenticatedUser()
    {
        $crawler = $this->client->request('GET', '/desktop');        
        $this->assertTrue($crawler->filter('#login_form')->count() > 0);
    }
    
    public function testAccessToDesktopSectionIsAllowedToSimpleUsers()
    {
        $this->logUser($this->users['user']);        
        $crawler = $this->client->request('GET', '/desktop');
        $this->assertTrue($crawler->filter('#desktop.section')->count() > 0);
    }
}