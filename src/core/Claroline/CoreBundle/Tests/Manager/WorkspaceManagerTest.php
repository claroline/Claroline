<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Testing\FunctionalTestCase;

class WorkspaceManagerTest extends FunctionalTestCase
{
    /** @var WorkspaceManager */
    private $workspaceManager;
    
    /** @var array[User] */
    private $users;
    
    public function setUp()
    {
        parent::setUp();
        $this->users = $this->loadUserFixture();
        $this->workspaceManager = $this->client->getContainer()->get('claroline.workspace_manager');
    }
    
    public function testManagerOfANewlyCreatedWorkspaceOwnsTheWorkspaceAndHasManagerRole()
    {
        $manager = $this->users['user'];
        $workspace = $this->workspaceManager->createWorkspace('Workspace test', $manager);
        
        $this->logUser($manager);
        
        $this->assertTrue($this->getSecurityContext()->isGranted('OWNER', $workspace));
        $this->assertTrue($this->getSecurityContext()->isGranted('ROLE_Workspace test manager'));
    }
    
    public function testANewlyCreatedWorkspaceHasADefaultUserRoleIfNoPublicRolesAreSpecified()
    {
        $manager = $this->users['user'];
        $this->workspaceManager->createWorkspace('Workspace test', $manager);
        
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $workspace = $em->getRepository('Claroline\CoreBundle\Entity\Workspace')
            ->findOneByName('Workspace test');
        
        $roleNames = array();
        
        foreach ($workspace->getRoles() as $role)
        {
            $roleNames[] = $role->getName();
        }
        
        $this->assertTrue(in_array('ROLE_Workspace test user', $roleNames));
    }
    
    public function testASimpleUserCanBeAdded()
    {
        $manager = $this->users['user'];
        $user = $this->users['admin'];
        $workspace = $this->workspaceManager->createWorkspace('Workspace test', $manager);
        $this->workspaceManager->addSimpleUser($workspace, $user);
        
        $this->logUser($user);
        $this->assertTrue($this->getSecurityContext()->isGranted('ROLE_Workspace test user'));
    }
}