<?php

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class DesktopSecurityTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
    }

    public function testDesktopSectionRequiresAuthenticatedUser()
    {
        $this->markTestSkipped(
            'is this test still relevant now anonymous can
            see the platform and a user must have a default tool in its workspace'
        );
        $crawler = $this->client->request('GET', '/desktop');
        $this->assertTrue($crawler->filter('#login-form')->count() > 0);
    }

    public function testAccessToDesktopSectionIsAllowedToSimpleUsers()
    {
        $this->markTestSkipped(
            'is this test still relevant now anonymous can
            see the platform and a user must have a default tool in its workspace'
        );
        $this->loadUserFixture(array('user'));
        $this->logUser($this->getFixtureReference('user/user'));
        $crawler = $this->client->request('GET', '/desktop');
        $this->assertTrue($crawler->filter('#link-desktop')->count() > 0);
    }
}