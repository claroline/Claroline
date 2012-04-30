<?php

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class AdminSecurityTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
    }
    
    public function testAdminSectionRequiresAuthenticatedUser()
    {
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('#login_form')->count() > 0);
    }
    
    public function testAccessToAdminSectionIsDeniedToSimpleUsers()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/admin');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }
    
    public function testAccessToAdminSectionIsAllowedToAdminUsers()
    {
        $this->markTestIncomplete(
          "login form layout not done yet"
        );
        
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('.administration')->count() > 0);
    }
}