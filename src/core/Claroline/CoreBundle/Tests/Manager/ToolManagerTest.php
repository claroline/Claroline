<?php

namespace Claroline\CoreBundle\Manager;

use Mockery as m;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class ToolManagerTest extends MockeryTestCase
{
    private $orderedToolRepo;
    private $toolRepo;
    private $ed;
    private $utilities;
    private $roleRepo;
    private $om;

    public function setUp()
    {
        parent::setUp();
        $this->orderedToolRepo = $this->mock('Claroline\CoreBundle\Repository\OrderedToolRepository');
        $this->toolRepo = $this->mock('Claroline\CoreBundle\Repository\ToolRepository');
        $this->ed = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->utilities = $this->mock('Claroline\CoreBundle\Library\Utilities\ClaroUtilities');
        $this->roleRepo = $this->mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
    }

    public function testCreate()
    {
        $tool = new Tool();
        $manager = $this->getManager();
        $this->om->shouldReceive('persist')->once()->with($tool);
        $this->om->shouldReceive('flush');
        $manager->create($tool);
    }

    public function testImport()
    {
        $manager = $this->getManager(
            array('createOrderedTool', 'addRoleToOrderedTool', 'addWorkspaceTool', 'extractFiles')
        );
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $config = array();
        $files = array('path');
        $roles = array($roleA, $roleB);
        $genRoles = array($roleA, $roleB);
        $arch = 'path/to/arch';
        $name = 'toolName';
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $userManager = $this->mock('Claroline\CoreBundle\Entity\User');
        $position = 1;
        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $orderedTool = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $manager->shouldReceive('extractFiles')->once()->with($arch, $config)->andReturn($files);
        $manager->shouldReceive('addWorkspaceTool')->with($tool, $position, $name, $workspace)
            ->once()->andReturn($orderedTool);
        $manager->shouldReceive('addRoleToOrderedTool')->times(2);
        $tool->shouldReceive('getName')->once()->andReturn('claro_tool');
        $this->ed->shouldReceive('dispatch')->once()
            ->with('tool_claro_tool_from_template', 'ImportTool', m::any());

        $manager->import(
            $config,
            $roles,
            $genRoles,
            $name,
            $workspace,
            $resource,
            $tool,
            $userManager,
            $position,
            $arch
        );
    }

    /**
     * @dataProvider addWorkspaceToolProvider
     */
    public function testAddWorkspaceTool($switchTool, $isExceptionExpected)
    {
        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $position = 1;

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('workspace' => $workspace, 'order' => $position))->andReturn($switchTool);

        if ($isExceptionExpected) {
            $this->setExpectedException('Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException');
        } else {
            $otr = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
            $this->om->shouldReceive('factory')->once()
                ->with('Claroline\CoreBundle\Entity\Tool\OrderedTool')->andReturn($otr);
            $otr->shouldReceive('setWorkspace')->once()->with($workspace);
            $otr->shouldReceive('setName')->once()->with('tool');
            $otr->shouldReceive('setOrder')->once()->with(1);
            $otr->shouldReceive('setTool')->once()->with($tool);
            $this->om->shouldReceive('persist')->once()->with($otr);
            $this->om->shouldReceive('flush')->once();
        }

        $this->getManager()->addWorkspaceTool($tool, 1, 'tool', $workspace);
    }

    public function testAddRole()
    {
        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $otr = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('tool' => $tool, 'workspace' => $workspace))->andReturn($otr);
        $otr->shouldReceive('addRole')->once()->with($role);
        $this->om->shouldReceive('persist')->with($otr)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->addRole($tool, $role, $workspace);
    }

    public function testAddRoleOrderedTool()
    {
        $otr = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');

        $otr->shouldReceive('addRole')->once()->with($role);
        $this->om->shouldReceive('persist')->with($otr)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->addRoleToOrderedTool($otr, $role);
    }

    public function testRemoveRole()
    {
        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $otr = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('tool' => $tool, 'workspace' => $workspace))->andReturn($otr);
        $otr->shouldReceive('removeRole')->once()->with($role);
        $this->om->shouldReceive('persist')->with($otr)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->removeRole($tool, $role, $workspace);
    }

    public function testRemoveRoleFromOrderedTool()
    {
        $otr = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');

        $otr->shouldReceive('removeRole')->once()->with($role);
        $this->om->shouldReceive('persist')->with($otr)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->removeRoleFromOrderedTool($otr, $role);
    }

    public function testGetDisplayedDesktopOrderedTools()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');

        $this->toolRepo->shouldReceive('findDesktopDisplayedToolsByUser')->with($user)->once();
        $this->getManager()->getDisplayedDesktopOrderedTools($user);
    }

    public function testGetDesktopToolsConfigurationArray()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $desktopToolA = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $desktopToolB = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $undisplayedTool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $toolA = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $toolB = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->orderedToolRepo->shouldReceive('findByUser')->once()->with($user)
            ->andReturn(array($desktopToolA, $desktopToolB));
        m::getConfiguration()->allowMockingNonExistentMethods(false);
        //unmapped field
        $desktopToolA->shouldReceive('getOrder')->once()->andReturn(1);
        $desktopToolA->shouldReceive('getTool')->times(2)->andReturn($toolA);
        $desktopToolB->shouldReceive('getOrder')->once()->andReturn(3);
        $desktopToolB->shouldReceive('getTool')->times(2)->andReturn($toolB);
        $toolA->shouldReceive('setVisible')->once()->with(true);
        $toolB->shouldReceive('setVisible')->once()->with(true);

        $this->toolRepo->shouldReceive('findDesktopUndisplayedToolsByUser')
            ->once()->with($user)->andReturn(array($undisplayedTool));
        $undisplayedTool->shouldReceive('setVisible')->once()->with(false);
        $this->utilities->shouldReceive('arrayFill')
            ->with(array('1' => $toolA, '3' => $toolB), array($undisplayedTool))->once();

        $this->getManager()->getDesktopToolsConfigurationArray($user);
    }

    /**
     * @dataProvider removeDesktopToolProvider
     */
    public function testRemoveDesktopTool($name, $isExceptionExpected)
    {
        $removedTool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $ot = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $removedTool->shouldReceive('getName')->once()->andReturn($name);

        if ($isExceptionExpected) {
            $this->setExpectedException('Claroline\CoreBundle\Manager\Exception\UnremovableToolException');
        } else {
            $this->orderedToolRepo->shouldReceive('findOneBy')
                ->once()
                ->with(array('user' => $user, 'tool' => $removedTool))
                ->andReturn($ot);
            $this->om->shouldReceive('remove')->once();
            $this->om->shouldReceive('flush');
        }

        $this->getManager()->removeDesktopTool($removedTool, $user);
    }

    /**
     * @dataProvider addDesktopToolProvider
     */
    public function testAddDesktopTool($switchTool, $isExceptionExpected)
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $position = 1;

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('user' => $user, 'order' => $position))->andReturn($switchTool);

        if ($isExceptionExpected) {
            $this->setExpectedException('Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException');
        } else {
            $otr = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
            $otr->shouldReceive('setUser')->once()->with($user);
            $otr->shouldReceive('setTool')->once()->with($tool);
            $otr->shouldReceive('setOrder')->once()->with($position);
            $otr->shouldReceive('setName')->once()->with('name');
            $this->om->shouldReceive('factory')->once()->with('Claroline\CoreBundle\Entity\Tool\OrderedTool')
                ->andReturn($otr);
            $this->om->shouldReceive('persist')->once()->with($otr);
            $this->om->shouldReceive('flush')->once();
        }

        $this->getManager()->addDesktopTool($tool, $user, $position, 'name');

    }

    public function testMove()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $position = 1;
        $movingTool = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $switchTool = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('user' => $user, 'tool' => $tool, 'workspace' => $workspace))->andReturn($movingTool);
        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('user' => $user, 'order' => $position, 'workspace' => $workspace))->andReturn($switchTool);
        $this->om->shouldReceive('persist')->with($movingTool)->once();
        $this->om->shouldReceive('persist')->with($switchTool)->once();
        $movingTool->shouldReceive('getOrder')->once()->andReturn(2);
        $movingTool->shouldReceive('setOrder')->with(1)->once();
        $switchTool->shouldReceive('setOrder')->with(2)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->move($tool, $position, $user, $workspace);
    }

    public function testAddMissingWorkspaceTools()
    {
        $manager = $this->getManager(array('addWorkspaceTool'));

        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $tool->shouldReceive('isDisplayableInWorkspace')->andReturn(true);
        $tool->shouldReceive('getName')->andReturn('displayedName');
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleA->shouldReceive('getId')->andReturn(1);
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB->shouldReceive('getId')->andReturn(2);
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $expected = array(
            array(
                'tool' => $tool,
                'visibility' => array(1 => false, 2 => false),
                'position' => 3,
                'workspace' => $workspace,
                'displayedName' => 'displayedName'
            )
        );

        $this->roleManager->shouldReceive('getWorkspaceRoles')->with($workspace)->once()->andReturn(array($roleA, $roleB));

        $this->toolRepo->shouldReceive('countDisplayedToolsByWorkspace')->once()->andReturn(2);
        $this->toolRepo->shouldReceive('findUndisplayedToolsByWorkspace')->once()->andReturn(array($tool));
        $this->orderedToolRepo->shouldReceive('findOneBy')
            ->with(array('workspace' => $workspace, 'tool' => $tool))->andReturn(null);
        $this->translator->shouldReceive('trans')->once();
        $manager->shouldReceive('addWorkspaceTool')->once();

        $this->assertEquals($expected, $manager->addMissingWorkspaceTools($workspace));
    }

    public function testGetWorkspaceExistingTools()
    {
        $manager = $this->getManager();

        $tool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleA->shouldReceive('getId')->andReturn(1);
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB->shouldReceive('getId')->andReturn(2);

        $ot = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $ot->shouldReceive('getTool')->andReturn($tool);
        $tool->shouldReceive('isDisplayableInWorkspace')->andReturn(true);
        $ot->shouldReceive('getRoles')->andReturn(array($roleA));
        $ot->shouldReceive('getOrder')->andReturn(1);
        $ot->shouldReceive('getName')->andReturn('displayedName');

        $expected = array(
            array(
                'tool' => $tool,
                'visibility' => array(1 => true, 2 => false),
                'position' => 1,
                'workspace' => $workspace,
                'displayedName' => 'displayedName'
            )
        );

        $this->orderedToolRepo->shouldReceive('findBy')->andReturn(array($ot))->once();
        $this->roleManager->shouldReceive('getWorkspaceRoles')->with($workspace)->once()->andReturn(array($roleA, $roleB));
        $this->assertEquals($expected, $manager->getWorkspaceExistingTools($workspace));
    }

    public function testGetWorkspaceToolsConfigurationArray()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $manager = $this->getManager(array('addMissingWorkspaceTools', 'getWorkspaceExistingTools'));
        $manager->shouldReceive('addMissingWorkspaceTools')->with($workspace)->once()->andReturn(array('1'));
        $manager->shouldReceive('getWorkspaceExistingTools')->with($workspace)->once()->andReturn(array('2'));
        $expected = array('2', '1');
        $this->assertEquals($expected, $manager->getWorkspaceToolsConfigurationArray($workspace));
    }

    public function testGetOrderedToolsByWorkspaceAndRoles()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roleA = new Role();
        $roleB = new Role();
        $roles = array($roleA, $roleB);
        $orderedTools = array('ordered_tool_1', 'ordered_tool_2');

        $this->orderedToolRepo
            ->shouldReceive('findByWorkspaceAndRoles')
            ->with($workspace, $roles)
            ->once()
            ->andReturn($orderedTools);

        $this->assertEquals(
            $orderedTools,
            $this->getManager()->getOrderedToolsByWorkspaceAndRoles($workspace, $roles)
        );
    }

    public function testGetDisplayedByRolesAndWorkspace()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $roleA = new Role();
        $roleB = new Role();
        $roles = array($roleA, $roleB);
        $tools = array('tool_1', 'tool_2');

        $this->toolRepo
            ->shouldReceive('findDisplayedByRolesAndWorkspace')
            ->with($roles, $workspace)
            ->once()
            ->andReturn($tools);

        $this->assertEquals(
            $tools,
            $this->getManager()->getDisplayedByRolesAndWorkspace($roles, $workspace)
        );
    }

    public function testEditOrderedTool()
    {
        $entity = new OrderedTool();
        $this->om->shouldReceive('persist')->once()->with($entity);
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->editOrderedTool($entity);
    }

    public function testEditTool()
    {
        $entity = new Tool();
        $this->om->shouldReceive('persist')->once()->with($entity);
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->editTool($entity);
    }

    public function testGetAllTools()
    {
        $this->toolRepo->shouldReceive('findAll')->once()->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getAllTools());
    }

    public function testgetOneByWorkspaceAndTool()
    {
        $tool = new Tool();
        $ws = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace();

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('workspace' => $ws, 'tool' => $tool))->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getOneByWorkspaceAndTool($ws, $tool));
    }

    public function testAddRequiredToolsUser()
    {
        $manager = $this->getManager(array('addDesktopTool'));
        $user = new \Claroline\CoreBundle\Entity\User;
        $home = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $resmanager = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $parameters = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $home->shouldReceive('getName')->once()->andReturn('home');
        $resmanager->shouldReceive('getName')->once()->andReturn('resource_manager');
        $parameters->shouldReceive('getName')->once()->andReturn('parameters');

        $this->toolRepo->shouldReceive('findOneBy')->once()->with(array('name' => 'home'))->andReturn($home);
        $this->toolRepo->shouldReceive('findOneBy')->once()->with(array('name' => 'resource_manager'))->andReturn($resmanager);
        $this->toolRepo->shouldReceive('findOneBy')->once()->with(array('name' => 'parameters'))->andReturn($parameters);
        $manager->shouldReceive('addDesktopTool')->once()->with($home, $user, 1, 'home');
        $manager->shouldReceive('addDesktopTool')->once()->with($resmanager, $user, 2, 'resource_manager');
        $manager->shouldReceive('addDesktopTool')->once()->with($parameters, $user, 3, 'parameters');
        $this->om->shouldReceive('startFlushSuite')->once();
        $this->om->shouldReceive('persist')->once()->with($user);
        $this->om->shouldReceive('endFlushSuite')->once()->with($user);

        $manager->addRequiredToolsToUser($user);

    }

    public function testGetOneToolByName()
    {
        $name = 'name';
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->toolRepo->shouldReceive('findOneByName')->once()->with($name)->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getOneToolByName($name));
    }

    public function testGetToolByCriteria()
    {
        $criteria = array();
        $this->toolRepo->shouldReceive('findBy')->with($criteria)->once()->andReturn('return');

        $this->assertEquals('return', $this->getManager()->getToolByCriterias($criteria));
    }

    public function testExtractFiles()
    {
        $archpath = 'path';
        $archive = $this->mock('ZipArchive');
        $confTools = array('files' => array('path'));
        $this->om->shouldReceive('factory')->once()->with('ZipArchive')->andReturn($archive);
        $archive->shouldReceive('open')->once()->with($archpath);
        $archive->shouldReceive('extractTo')->once()->with(m::any());

        $realPath = $this->getManager()->extractFiles($archpath, $confTools);
        $this->assertContains('path', $realPath[0]);
    }

    public function removeDesktopToolProvider()
    {
        return array(
            array('name' => 'toolname', 'isExceptionExpected' => false),
            array('name' => 'parameters', 'isExceptionExpected' => true)
        );
    }

    public function addDesktopToolProvider()
    {
        $switchTool = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');

        return array(
            array('switchTool' => $switchTool, 'isExceptionExpected' => true),
            array('switchTool' => null, 'isExceptionExpected' => false)
        );
    }

    public function addWorkspaceToolProvider()
    {
        $switchTool = $this->mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');

        return array(
            array('switchTool' => $switchTool, 'isExceptionExpected' => true),
            array('switchTool' => null, 'isExceptionExpected' => false)
        );
    }

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:Tool\OrderedTool')->andReturn($this->orderedToolRepo);
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:Tool\Tool')->andReturn($this->toolRepo);
        $this->om->shouldReceive('getRepository')->once()
            ->with('ClarolineCoreBundle:Role')->andReturn($this->roleRepo);

        if (count($mockedMethods) === 0) {
            return new ToolManager(
                $this->ed,
                $this->utilities,
                $this->translator,
                $this->om,
                $this->roleManager
            );
        } else {
            $stringMocked = '[';
                $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return $this->mock(
                'Claroline\CoreBundle\Manager\ToolManager' . $stringMocked,
                array(
                    $this->ed,
                    $this->utilities,
                    $this->translator,
                    $this->om,
                    $this->roleManager
                )
            );
        }
    }
}
