<?php

namespace Claroline\CoreBundle\Library\Manager;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class WorkspaceManagerTest extends FunctionalTestCase
{
    /** @var WorkspaceManager */
    private $workspaceManager;
    
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture();
        $this->workspaceManager = $this->client->getContainer()->get('claroline.workspace_manager');
    }
    
    public function testManagerOfANewlyCreatedWorkspaceOwnsTheWorkspaceAndHasManagerRole()
    {
        $manager = $this->getFixtureReference('user/user');
        $workspace = $this->workspaceManager->createWorkspace('Workspace test', $manager);
        
        $this->logUser($manager);
        
        $this->assertTrue($this->getSecurityContext()->isGranted('OWNER', $workspace));
        $this->assertTrue($this->getSecurityContext()->isGranted($workspace->getManagerRole()->getName()));
    }
    
    public function testACollaboratorCanBeAdded()
    {
        $manager = $this->getFixtureReference('user/user');
        $workspace = $this->workspaceManager->createWorkspace('Workspace test', $manager);
        
        $user = $this->getFixtureReference('user/admin');
        $this->workspaceManager->addCollaborator($workspace, $user);
        
        $this->logUser($user);
        $this->assertTrue($this->getSecurityContext()->isGranted($workspace->getCollaboratorRole()->getName()));
    }
}