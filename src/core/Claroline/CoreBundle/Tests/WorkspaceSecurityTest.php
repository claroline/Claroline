<?php

namespace Claroline\CoreBundle;

use Claroline\CoreBundle\Testing\FunctionalTestCase;

class WorkspaceSecurityTest extends FunctionalTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->client->followRedirects();
    }
    
    public function testWorkspacesSectionRequiresAuthenticatedUser()
    {
        $crawler = $this->client->request('GET', '/workspaces');
        $this->assertTrue($crawler->filter('#login_form')->count() > 0);
    }
    
    public function testWorkspaceCreationIsReservedToWorkspaceCreators()
    {
        $this->logUser($this->getFixtureReference('user/user'));
        $this->client->request('GET', '/workspace/new/form');
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $crawler = $this->client->request('GET', '/workspace/new/form');
        $this->assertTrue($crawler->filter('#workspaces.section')->count() > 0);
    }
}