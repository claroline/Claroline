<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;

class ParametersControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->loadUserFixture(array('admin'));
    }

    public function testDesktopAddThenRemoveTool()
    {
        $repo = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool');

        $baseDisplayedTools = $repo->getDesktopTools($this->getFixtureReference('user/admin'));
        $nbBaseDisplayedTools = count($baseDisplayedTools);
        $home = $repo->findOneBy(array('name' => 'calendar'));
        $this->logUser($this->getFixtureReference('user/admin'));

        $this->client->request(
            'POST',
            "/desktop/tool/properties/add/tool/{$home->getId()}/position/4"
        );

        $this->assertEquals(
            ++$nbBaseDisplayedTools,
            count($repo->getDesktopTools($this->getFixtureReference('user/admin')))
        );

        $this->client->request(
            'POST',
            "/desktop/tool/properties/remove/tool/{$home->getId()}"
        );

        $this->assertEquals(
            --$nbBaseDisplayedTools,
            count($repo->getDesktopTools($this->getFixtureReference('user/admin')))
        );
    }

    public function testWorkspaceAddThenRemoveTool()
    {
         $repo = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool');

         $workspace = $this->getFixtureReference('user/admin')->getPersonalWorkspace();
         $role = $this->client
             ->getContainer()
             ->get('doctrine.orm.entity_manager')
             ->getRepository('ClarolineCoreBundle:Role')->getVisitorRole($workspace);

         $baseDisplayedTools = $repo->getToolsForRolesInWorkspace(array($role->getName()), $workspace);
         $nbBaseDisplayedTools = count($baseDisplayedTools);
         $calendar = $repo->findOneBy(array('name' => 'calendar'));
         $this->logUser($this->getFixtureReference('user/admin'));

         $toolId = $calendar->getId();
         $workspaceId = $workspace->getId();
         $roleId = $role->getId();

         $this->client->request(
             'POST',
             "/workspaces/tool/properties/add/tool/{$toolId}/position/4/workspace/{$workspaceId}/role/{$roleId}"
         );

         $this->assertEquals(
            ++$nbBaseDisplayedTools,
            count($repo->getToolsForRolesInWorkspace(array($role->getName()), $workspace))
        );

        $this->client->request(
            'POST',
            "/workspaces/tool/properties/remove/tool/{$toolId}/workspace/{$workspaceId}/role/{$roleId}"
        );

        $this->assertEquals(
            --$nbBaseDisplayedTools,
            count($repo->getToolsForRolesInWorkspace(array($role->getName()), $workspace))
        );
    }

    public function testMoveDesktopTool()
    {
        $home = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'home'));

        $parameters = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'parameters'));

        $this->logUser($this->getFixtureReference('user/admin'));

        $this->client->request(
            'POST',
            "/desktop/tool/properties/move/tool/{$home->getId()}/position/2"
        );

       $repo = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\DesktopTool');

        $this->assertEquals(
            2,
            $repo->findOneBy(array('tool' => $home, 'user' => $this->getFixtureReference('user/admin')))
                ->getOrder()
        );

        $this->assertEquals(
            1,
            $repo->findOneBy(array('tool' => $parameters, 'user' => $this->getFixtureReference('user/admin')))
               ->getOrder()
        );

    }

    public function testMoveWorkspaceTool()
    {
        $home = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'home')
        );

         $workspace = $this->getFixtureReference('user/admin')->getPersonalWorkspace();

         $role = $this->client
             ->getContainer()
             ->get('doctrine.orm.entity_manager')
             ->getRepository('ClarolineCoreBundle:Role')->getCollaboratorRole($workspace);

         $this->logUser($this->getFixtureReference('user/admin'));

         $workspaceId = $workspace->getId();
         $toolId = $home->getId();
         $roleId = $role->getId();
         $resourceManager = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'resource_manager')
         );

         $this->client->request(
            'POST',
            "/workspaces/tool/properties/move/tool/{$toolId}/position/2/workspace/{$workspaceId}/role/{$roleId}"
         );

         $repo = $this->client
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\WorkspaceToolRole');

        $this->assertEquals(
            2,
            $repo->findOneBy(array('tool' => $home, 'role' => $role, 'workspace' => $workspace))
                ->getOrder()
        );

        $this->assertEquals(
            1,
            $repo->findOneBy(array('tool' => $resourceManager, 'role' => $role, 'workspace' => $workspace))
               ->getOrder()
        );

    }
}

