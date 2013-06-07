<?php

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\FunctionalTestCase;
use Claroline\CoreBundle\Library\Installation\Plugin\Loader;

class ParametersControllerTest extends FunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->resetTemplate();
        $this->loadPlatformRoleData();
        $this->loadUserData(array('john' => 'user'));
    }

    public function testDesktopAddThenRemoveTool()
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $baseDisplayedTools = $repo->findByUser($this->getUser('john'), true);
        $nbBaseDisplayedTools = count($baseDisplayedTools);
        $calendar = $repo->findOneBy(array('name' => 'calendar'));
        $calendarPosition = $nbBaseDisplayedTools + 1;
        $this->logUser($this->getUser('john'));

        $this->client->request(
            'POST',
            "/desktop/tool/properties/add/tool/{$calendar->getId()}/position/{$calendarPosition}"
        );

        $this->assertEquals(
            ++$nbBaseDisplayedTools,
            count($repo->findByUser($this->getUser('john'), true))
        );

        $this->client->request(
            'POST',
            "/desktop/tool/properties/remove/tool/{$calendar->getId()}"
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

    public function testWorkspaceAddToolFromInstalledPlugin()
    {
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $workspaceOrderedTools = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findBy(array('workspace' => $this->getWorkspace('john')));
        $countOldOrderedTools = count($workspaceOrderedTools);
        $managerRole = $em->getRepository('ClarolineCoreBundle:Role')->findManagerRole($this->getWorkspace('john'));
        $this->registerStubPlugins(array('Valid\WithTools\ValidWithTools'));
        $toolA = $em->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('toolA');
        $this->logUser($this->getUser('john'));
        $position = $countOldOrderedTools + 1;
        $this->client->request(
            'POST',
            "/workspaces/tool/properties/add/tool/{$toolA->getId()}/" .
            "position/{$position}/workspace/{$this->getWorkspace('john')->getId()}/role/{$managerRole->getId()}"
        );
        $workspaceOrderedTools = $em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findBy(array('workspace' => $this->getWorkspace('john')));
        $countNewOrderedTools = count($workspaceOrderedTools);
        $this->assertEquals(1, $countNewOrderedTools - $countOldOrderedTools);

        $this->resetTemplate();
    }

    public function testWorkspaceToolFromInstalledPluginInToolsConfigurationPage()
    {
        $this->registerStubPlugins(array('Valid\WithTools\ValidWithTools'));
        $this->logUser($this->getUser('john'));
        $wsId = $this->getWorkspace('john')->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$wsId}/tools"
        );
        $items = $crawler->filter('td:contains("toolA")');
        $this->assertEquals(count($items), 1);
        $this->resetTemplate();
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

    public function testWSCreatorCanEditWS()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(
            array(
                'ws_a' => 'ws_creator',
                'ws_b' => 'ws_creator',
            )
        );
        $this->logUser($this->getUser('ws_creator'));
        $wsBId = $this->getWorkspace('ws_b')->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$wsBId}/editform"
        );
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_edit_form[name]'] = 'modified_name';
        $form['workspace_edit_form[code]'] = 'modified_code';
        $this->client->submit($form);

        $crawler = $this->client->request(
            'GET',
            "/workspaces/user"
        );

        $this->assertEquals(3, $crawler->filter('.row-workspace')->count());

        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$wsBId}/editform"
        );
        $this->assertEquals('modified_name', $crawler->filter('#workspace_edit_form_name')->attr('value'));
        $this->assertEquals('modified_code', $crawler->filter('#workspace_edit_form_code')->attr('value'));
    }

    public function testWSCreatorCantEditWSWithExistingCode()
    {
        $this->loadUserData(array('ws_creator' => 'ws_creator'));
        $this->loadWorkspaceData(
            array(
                'ws_a' => 'ws_creator',
                'ws_b' => 'ws_creator',
            )
        );
        $this->logUser($this->getFixtureReference('user/ws_creator'));
        $wsBId = $this->getFixtureReference('workspace/ws_b')->getId();
        $wsBCode = $this->getFixtureReference('workspace/ws_b')->getCode();
        $wsACode = $this->getFixtureReference('workspace/ws_a')->getCode();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$wsBId}/editform"
        );
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_edit_form[name]'] = 'modified_name';
        $form['workspace_edit_form[code]'] = $wsACode;
        $this->client->submit($form);

        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$wsBId}/editform"
        );
        $this->assertEquals($wsBCode, $crawler->filter('#workspace_edit_form_code')->attr('value'));
    }

    public function testRenameWorkspaceOrderTool()
    {
        $home = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'home'));
        $workspace = $this->getWorkspace('john');
        $this->logUser($this->getUser('john'));
        $wot = $this->em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findOneBy(array('tool' => $home, 'workspace' => $workspace));

        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$workspace->getId()}/tools/{$wot->getId()}/editform"
        );
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_order_tool_edit_form[name]'] = 'modified_name';
        $this->client->submit($form);
        $this->em->clear();

        $newName = $this->em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findOneBy(array('tool' => $home, 'workspace' => $workspace))
            ->getName();

        $this->assertEquals(
            'modified_name',
            $newName
        );
    }

    public function testCantRenameWorkspaceOrderToolWithAnotherWOTName()
    {
        $home = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'home'));
        $workspace = $this->getWorkspace('john');
        $this->logUser($this->getUser('john'));
        $wot = $this->em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findOneBy(array('tool' => $home, 'workspace' => $workspace));
        $oldName = $wot->getName();

        $resourceManager = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool')
            ->findOneBy(array('name' => 'resource_manager'));
        $rmName = $this->em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findOneBy(array('tool' => $resourceManager, 'workspace' => $workspace))
            ->getName();

        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$workspace->getId()}/tools/{$wot->getId()}/editform"
        );
        $form = $crawler->filter('button[type=submit]')->form();
        $form['workspace_order_tool_edit_form[name]'] = $rmName;
        $this->client->submit($form);
        $this->em->clear();

        $newName = $this->em->getRepository('ClarolineCoreBundle:Tool\WorkspaceOrderedTool')
            ->findOneBy(array('tool' => $home, 'workspace' => $workspace))
            ->getName();

        $this->assertEquals(
            $oldName,
            $newName
        );
    }

    public function testWorkspaceToolsRolesDisplaysTheCorrectTableAction()
    {
        $this->logUser($this->getUser('john'));
        $wsId = $this->getWorkspace('john')->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$wsId}/tools"
        );
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $roleCollaborator = $em->getRepository('ClarolineCoreBundle:Role')
            ->findCollaboratorRole($this->getWorkspace('john'));

        $tools = $crawler
            ->filter("td[data-role-id={$roleCollaborator->getId()}]");
        $checkedTools = $crawler
            ->filter("td[data-role-id={$roleCollaborator->getId()}] input:checked[type='checkbox']");
        $uncheckedTools = $crawler
            ->filter("td[data-role-id={$roleCollaborator->getId()}] input:not(:checked[type='checkbox'])");

        $this->assertEquals(3, count($checkedTools));
        $this->assertEquals(count($tools) - count($checkedTools), count($uncheckedTools));
    }

    public function testWorkspaceResourceRightsCreationFormAfterPluginInstall()
    {
        $this->markTestSkipped('new entity namespace = ?');
        $pluginFqcn = 'Valid\WithCustomResources\ValidWithCustomResources';
        $ds = DIRECTORY_SEPARATOR;
        require_once __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}"
            . "WithCustomResources{$ds}Entity{$ds}ResourceA.php";
        require_once __DIR__."{$ds}..{$ds}..{$ds}Stub{$ds}plugin{$ds}Valid{$ds}"
            . "WithCustomResources{$ds}Entity{$ds}ResourceB.php";
        $this->registerStubPlugins(array($pluginFqcn));
        $this->logUser($this->getUser('john'));
        $em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $managerRole = $em->getRepository('ClarolineCoreBundle:Role')->findManagerRole($this->getWorkspace('john'));
        $wsId = $this->getWorkspace('john')->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$wsId}/resource/rights/form/role/{$managerRole->getId()}"
        );
        $this->assertEquals(
            1,
            count($crawler->filter('input:not(:checked[type="checkbox"])'))
        );
        $this->resetTemplate();
    }

    public function testWorkspaceExportForm()
    {
        $this->logUser($this->getUser('john'));
        $wsId = $this->getWorkspace('john')->getId();
        $crawler = $this->client->request(
            'GET',
            "/workspaces/tool/properties/{$wsId}/form/export"
        );
        $this->assertEquals(1, count($crawler->filter('#workspace_template_form')));
    }

    public function testDesktopConfigureToolsPage()
    {
        $repo = $this->em->getRepository('ClarolineCoreBundle:Tool\Tool');
        $activeTools = $repo->findByUser($this->getUser('john'), true);
        $this->logUser($this->getUser('john'));
        $crawler = $this->client->request(
            'GET',
            '/desktop/tool/properties/tools'
        );
        $this->assertEquals(count($activeTools), count($crawler->filter('input:checked[type="checkbox"]')));
    }

    public function testRemoveParametersFromDesktop()
    {
        $this->logUser($this->getUser('john'));
        $tool = $this->client->getContainer()->get('doctrine.orm.entity_manager')
            ->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneByName('parameters');
        $this->client->request(
            'POST',
            "desktop/tool/properties/remove/tool/{$tool->getId()}"
        );
        $this->assertContains('remove the parameter', $this->client->getResponse()->getContent());
    }
}