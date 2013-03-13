<?php

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class AdminSecurityTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
        $this->loadPlatformRolesFixture();
    }

    public function testAdminSectionRequiresAuthenticatedUser()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('#login-form')->count() > 0);
    }

    public function testAccessToAdminSectionIsDeniedToSimpleUsers()
    {
        $this->loadUserData(array('user' => 'user'));
        $this->logUser($this->getUser('user'));
        $this->client->request('GET', '/admin');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAccessToAdminSectionIsAllowedToAdminUsers()
    {
        $this->loadUserData(array('admin' => 'admin'));
        $this->logUser($this->getUser('admin'));
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('.administration')->count() > 0);
    }
}