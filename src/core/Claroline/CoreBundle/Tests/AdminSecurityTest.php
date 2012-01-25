<?php

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\Testing\FunctionalTestCase;

class AdminSecurityTest extends FunctionalTestCase
{
    /** @var array[User] */
    private $users;
    
    public function setUp()
    {
        parent::setUp();
        $this->users = $this->loadUserFixture();
        $this->client->followRedirects();
    }
    
    public function testAdminSectionRequiresAuthenticatedUser()
    {
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('#login_form')->count() > 0);
    }
    
    public function testAccessToAdminSectionIsDeniedToSimpleUsers()
    {
        $this->logUser($this->users['user']);
        $this->client->request('GET', '/admin');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testAccessToAdminSectionIsAllowedToAdminUsers()
    {
        $this->logUser($this->users['admin']);
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('#administration.section')->count() > 0);
    }
}