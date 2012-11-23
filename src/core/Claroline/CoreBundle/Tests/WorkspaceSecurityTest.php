<?php

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceSecurityTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->client->followRedirects();
    }

    public function testWorkspacesSectionRequiresAuthenticatedUser()
    {
        $crawler = $this->client->request('GET', '/workspaces');
        $this->assertTrue($crawler->filter('#login-form')->count() > 0);
    }

    public function testWorkspaceCreationIsReservedToWorkspaceCreators()
    {
        $this->loadUserFixture(array('user', 'ws_creator'));
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/workspaces/new/form');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request('GET', '/workspaces/new/form');
        $this->assertTrue($crawler->filter('#ws_creation_form')->count() > 0);
    }
}