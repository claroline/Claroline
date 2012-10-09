<?php

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class DashboardSecurityTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
    }

    public function testDashboardSectionRequiresAuthenticatedUser()
    {
        $crawler = $this->client->request('GET', '/dashboard');
        $this->assertTrue($crawler->filter('#login-form')->count() > 0);
    }

    public function testAccessToDashboardSectionIsAllowedToSimpleUsers()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', '/dashboard');
        $this->assertTrue($crawler->filter('#link-dashboard')->count() > 0);
    }
}