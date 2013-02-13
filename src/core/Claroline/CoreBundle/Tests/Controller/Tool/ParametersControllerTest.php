<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class ParametersControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user'));
    }

    public function testDesktopAddThenRemoveTool()
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $baseDisplayedTools = $repo->findByUser($this->getUser('john'), true);
        $nbBaseDisplayedTools = count($baseDisplayedTools);
        $home = $repo->findOneBy(array('name' => 'calendar'));
        $this->logUser($this->getUser('john'));

        $this->client->request(
            'POST',
            "/desktop/tool/properties/add/tool/{$home->getId()}/position/4"
        );

        $this->assertEquals(
            ++$nbBaseDisplayedTools,
            count($repo->findByUser($this->getUser('john'), true))
        );

        $this->client->request(
            'POST',
            "/desktop/tool/properties/remove/tool/{$home->getId()}"
        );

        $this->assertEquals(
            --$nbBaseDisplayedTools,
            count($repo->findByUser($this->getUser('john'), true))
        );
    }

    public function testWorkspaceAddThenRemoveTool()
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $workspace = $this->getWorkspace('john');
        $role = $this->em->getRepository('ClarolineCoreBundle:Role')
            ->findVisitorRole($workspace);
        $baseDisplayedTools = $repo->findByRolesAndWorkspace(array($role->getName()), $workspace, true);
        $nbBaseDisplayedTools = count($baseDisplayedTools);
        $calendar = $repo->findOneBy(array('name' => 'calendar'));
        $this->logUser($this->getUser('john'));

        $toolId = $calendar->getId();
        $workspaceId = $workspace->getId();
        $roleId = $role->getId();

        $this->client->request(
            'POST',
            "/workspaces/tool/properties/add/tool/{$toolId}/position/4/workspace/{$workspaceId}/role/{$roleId}"
        );

        $this->assertEquals(
            ++$nbBaseDisplayedTools,
            count($repo->findByRolesAndWorkspace(array($role->getName()), $workspace, true))
        );

        $this->client->request(
            'POST',
            "/workspaces/tool/properties/remove/tool/{$toolId}/workspace/{$workspaceId}/role/{$roleId}"
        );

        $this->assertEquals(
            --$nbBaseDisplayedTools,
            count($repo->findByRolesAndWorkspace(array($role->getName()), $workspace, true))
        );
    }

    public function testMoveDesktopTool()
    {
        $toolRepo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $home = $toolRepo->findOneBy(array('name' => 'home'));
        $parameters = $toolRepo->findOneBy(array('name' => 'parameters'));
        $resources = $toolRepo->findOneBy(array('name' => 'resource_manager'));
        $desktopToolRepo = $this->em->getRepository('ClarolineCoreBundle:Tool\DesktopTool');

        $this->logUser($this->getUser('john'));
        $this->client->request(
            'POST',
            "/desktop/tool/properties/move/tool/{$home->getId()}/position/2"
        );

        $this->em->clear();
        $this->assertEquals(
            2,
            $desktopToolRepo->findOneBy(array('tool' => $home, 'user' => $this->getUser('john')))
                ->getOrder()
        );
        $this->assertEquals(
            1,
            $desktopToolRepo->findOneBy(array('tool' => $resources, 'user' => $this->getUser('john')))
               ->getOrder()
        );
        $this->assertEquals(
            3,
            $desktopToolRepo->findOneBy(array('tool' => $parameters, 'user' => $this->getUser('john')))
               ->getOrder()
        );
    }

    public function testMoveWorkspaceTool()
    {
        $home = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'home'));
        $workspace = $this->getWorkspace('john');
        $this->logUser($this->getUser('john'));
        $resourceManager = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')
           ->findOneBy(array('name' => 'resource_manager'));

        $this->client->request(
            'POST',
            "/workspaces/tool/properties/move/tool/{$home->getId()}/position/2/workspace/{$workspace->getId()}"
        );

        $this->em->clear();
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool');

        $this->assertEquals(
            2,
            $repo->findOneBy(array('tool' => $home, 'workspace' => $workspace))
                ->getOrder()
        );
        $this->assertEquals(
            1,
            $repo->findOneBy(array('tool' => $resourceManager, 'workspace' => $workspace))
               ->getOrder()
        );
    }
}