<?php

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class AdminSecurityTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
    }

    public function testAdminSectionRequiresAuthenticatedUser()
    {
        $this->loadUserFixture(array('admin'));
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('#login-form')->count() > 0);
    }

    public function testAccessToAdminSectionIsDeniedToSimpleUsers()
    {
        $this->loadUserFixture(array('user'));
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/admin');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAccessToAdminSectionIsAllowedToAdminUsers()
    {
        $this->loadUserFixture(array('admin'));
        $this->logUser($this->getFixtureReference('user/admin'));
        $crawler = $this->client->request('GET', '/admin');
        $this->assertTrue($crawler->filter('.administration')->count() > 0);
    }
}