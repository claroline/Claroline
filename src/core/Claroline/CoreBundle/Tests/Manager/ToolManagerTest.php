<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Entity\Tool\Tool;
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
        $this->orderedToolRepo = m::mock('Claroline\CoreBundle\Repository\OrderedToolRepository');
        $this->toolRepo = m::mock('Claroline\CoreBundle\Repository\ToolRepository');
        $this->ed = m::mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->utilities = m::mock('Claroline\CoreBundle\Library\Utilities\ClaroUtilities');
        $this->roleRepo = m::mock('Claroline\CoreBundle\Repository\RoleRepository');
        $this->translator = m::mock('Symfony\Component\Translation\Translator');
        $this->om = m::mock('Claroline\CoreBundle\Persistence\ObjectManager');
    }

    /**
     * @group tool
     */
    public function testCreate()
    {
        $tool = new Tool();
        $manager = $this->getManager();
        $this->om->shouldReceive('persist')->once()->with($tool);
        $this->om->shouldReceive('flush');
        $manager->create($tool);
    }

    /**
     * @group tool
     * @group workspace
     */
    public function testImport()
    {
        $manager = $this->getManager(array('createOrderedTool', 'addRoleToOrderedTool', 'addWorkspaceTool'));
        $role1 = m::mock('Claroline\CoreBundle\Entity\Role');
        $role2 = m::mock('Claroline\CoreBundle\Entity\Role');
        $config = array();
        $files = array('path');
        $roles = array($role1, $role2);
        $name = 'toolName';
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $resource = m::mock('Claroline\CoreBundle\Entity\Resource\Directory');
        $userManager = m::mock('Claroline\CoreBundle\Entity\User');
        $position = 1;
        $tool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $orderedTool = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');

        $manager->shouldReceive('addWorkspaceTool')->with($tool, $position, $name, $workspace)
            ->once()->andReturn($orderedTool);
        $manager->shouldReceive('addRoleToOrderedTool')->times(2);
        $tool->shouldReceive('getName')->once()->andReturn('claro_tool');
        $this->ed->shouldReceive('dispatch')->once()
            ->with('tool_claro_tool_from_template', 'ImportTool', m::any());

        $manager->import($config, $roles, $files, $name, $workspace, $resource, $tool, $userManager, $position);
    }

    /**
     * @group tool
     * @group workspace
     * @dataProvider addWorkspaceToolProvider
     */
    public function testAddWorkspaceTool($switchTool, $isExceptionExpected)
    {
        $tool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $position = 1;

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('workspace' => $workspace, 'order' => $position))->andReturn($switchTool);

        if ($isExceptionExpected) {
            $this->setExpectedException('Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException');
        } else {
            $otr = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
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

    /**
     * @group tool
     */
    public function testAddRole()
    {
        $tool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $otr = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $role = m::mock('Claroline\CoreBundle\Entity\Role');

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('tool' => $tool, 'workspace' => $workspace))->andReturn($otr);
        $otr->shouldReceive('addRole')->once()->with($role);
        $this->om->shouldReceive('persist')->with($otr)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->addRole($tool, $role, $workspace);
    }

    /**
     * @group tool
     */
    public function testAddRoleOrderedTool()
    {
        $otr = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $role = m::mock('Claroline\CoreBundle\Entity\Role');

        $otr->shouldReceive('addRole')->once()->with($role);
        $this->om->shouldReceive('persist')->with($otr)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->addRoleToOrderedTool($otr, $role);
    }

    /**
     * @group tool
     */
    public function testRemoveRole()
    {
        $tool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $otr = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $role = m::mock('Claroline\CoreBundle\Entity\Role');

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('tool' => $tool, 'workspace' => $workspace))->andReturn($otr);
        $otr->shouldReceive('removeRole')->once()->with($role);
        $this->om->shouldReceive('persist')->with($otr)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->removeRole($tool, $role, $workspace);
    }

    /**
     * @group tool
     */
    public function testRemoveRoleFromOrderedTool()
    {
        $otr = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $role = m::mock('Claroline\CoreBundle\Entity\Role');

        $otr->shouldReceive('removeRole')->once()->with($role);
        $this->om->shouldReceive('persist')->with($otr)->once();
        $this->om->shouldReceive('flush')->once();
        $this->getManager()->removeRoleFromOrderedTool($otr, $role);
    }

    /**
     * @group tool
     * @group user
     */
    public function testGetDisplayedDesktopOrderedTools()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');

        $this->toolRepo->shouldReceive('findDesktopDisplayedToolsByUser')->with($user)->once();
        $this->getManager()->getDisplayedDesktopOrderedTools($user);
    }

    /**
     * @group tool
     * @group user
     */
    public function testGetDesktopToolsConfigurationArray()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $desktopTool1 = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $desktopTool3 = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $undisplayedTool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $tool1 = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $tool3 = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');

        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->orderedToolRepo->shouldReceive('findByUser')->once()->with($user)
            ->andReturn(array($desktopTool1, $desktopTool3));
        m::getConfiguration()->allowMockingNonExistentMethods(false);
        //unmapped field
        $desktopTool1->shouldReceive('getOrder')->once()->andReturn(1);
        $desktopTool1->shouldReceive('getTool')->times(2)->andReturn($tool1);
        $desktopTool3->shouldReceive('getOrder')->once()->andReturn(3);
        $desktopTool3->shouldReceive('getTool')->times(2)->andReturn($tool3);
        $tool1->shouldReceive('setVisible')->once()->with(true);
        $tool3->shouldReceive('setVisible')->once()->with(true);

        $this->toolRepo->shouldReceive('findDesktopUndisplayedToolsByUser')
            ->once()->with($user)->andReturn(array($undisplayedTool));
        $undisplayedTool->shouldReceive('setVisible')->once()->with(false);
        $this->utilities->shouldReceive('arrayFill')
            ->with(array('1' => $tool1, '3' => $tool3), array($undisplayedTool))->once();

        $this->getManager()->getDesktopToolsConfigurationArray($user);
    }

    /**
     * @group tool
     * @group user
     * @dataProvider removeDesktopToolProvider
     *
     */
    public function testRemoveDesktopTool($name, $isExceptionExpected)
    {
        $removedTool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $ot = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $removedTool->shouldReceive('getName')->once()->andReturn($name);

        if ($isExceptionExpected) {
            $this->setExpectedException('Claroline\CoreBundle\Manager\Exception\UnremovableToolException');
        } else {
            $this->orderedToolRepo->shouldReceive('findOneBy')->once()->with(array('user' => $user, 'tool' => $removedTool))
                ->andReturn($ot);
            $this->om->shouldReceive('remove')->once();
            $this->om->shouldReceive('flush');
        }

        $this->getManager()->removeDesktopTool($removedTool, $user);
    }

    /**
     * @group tool
     * @group user
     * @dataProvider addDesktopToolProvider
     */
    public function testAddDesktopTool($switchTool, $isExceptionExpected)
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $tool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $position = 1;

        $this->orderedToolRepo->shouldReceive('findOneBy')->once()
            ->with(array('user' => $user, 'order' => $position))->andReturn($switchTool);

        if ($isExceptionExpected) {
            $this->setExpectedException('Claroline\CoreBundle\Manager\Exception\ToolPositionAlreadyOccupiedException');
        } else {
            $otr = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
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

    /**
     * @group tool
     */
    public function testMove()
    {
        $user = m::mock('Claroline\CoreBundle\Entity\User');
        $tool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $position = 1;
        $movingTool = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $switchTool = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

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

    /**
     * @group tool
     * @group workspace
     */
    public function testAddMissingWorkspaceTools()
    {
        $manager = $this->getManager(array('getWorkspaceRoles', 'addWorkspaceTool'));

        $tool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $tool->shouldReceive('isDisplayableInWorkspace')->andReturn(true);
        $tool->shouldReceive('getName')->andReturn('displayedName');
        $role1 = m::mock('Claroline\CoreBundle\Entity\Role');
        $role1->shouldReceive('getId')->andReturn(1);
        $role2 = m::mock('Claroline\CoreBundle\Entity\Role');
        $role2->shouldReceive('getId')->andReturn(2);
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $expected = array(
            array(
                'tool' => $tool,
                'visibility' => array(1 => false, 2 => false),
                'position' => 3,
                'workspace' => $workspace,
                'displayedName' => 'displayedName'
            )
        );

        $manager->shouldReceive('getWorkspaceRoles')->with($workspace)->once()->andReturn(array($role1, $role2));

        $this->toolRepo->shouldReceive('countDisplayedToolsByWorkspace')->once()->andReturn(2);
        $this->toolRepo->shouldReceive('findUndisplayedToolsByWorkspace')->once()->andReturn(array($tool));
        $this->orderedToolRepo->shouldReceive('findOneBy')
            ->with(array('workspace' => $workspace, 'tool' => $tool))->andReturn(null);
        $this->translator->shouldReceive('trans')->once();
        $manager->shouldReceive('addWorkspaceTool')->once();

        $this->assertEquals($expected, $manager->addMissingWorkspaceTools($workspace));
    }

    /**
     * @group tool
     * @group workspace
     */
    public function testGetWorkspaceExistingTools()
    {
        $manager = $this->getManager(array('getWorkspaceRoles'));

        $tool = m::mock('Claroline\CoreBundle\Entity\Tool\Tool');
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $role1 = m::mock('Claroline\CoreBundle\Entity\Role');
        $role1->shouldReceive('getId')->andReturn(1);
        $role2 = m::mock('Claroline\CoreBundle\Entity\Role');
        $role2->shouldReceive('getId')->andReturn(2);

        $ot = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');
        $ot->shouldReceive('getTool')->andReturn($tool);
        $tool->shouldReceive('isDisplayableInWorkspace')->andReturn(true);
        $ot->shouldReceive('getRoles')->andReturn(array($role1));
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
        $manager->shouldReceive('getWorkspaceRoles')->with($workspace)->once()->andReturn(array($role1, $role2));
        $this->assertEquals($expected, $manager->getWorkspaceExistingTools($workspace));
    }

    /**
     * @group tool
     * @group workspace
     */
    public function testGetWorkspaceToolsConfigurationArray()
    {
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $manager = $this->getManager(array('addMissingWorkspaceTools', 'getWorkspaceExistingTools'));
        $manager->shouldReceive('addMissingWorkspaceTools')->with($workspace)->once()->andReturn(array('1'));
        $manager->shouldReceive('getWorkspaceExistingTools')->with($workspace)->once()->andReturn(array('2'));
        $expected = array('2', '1');
        $this->assertEquals($expected, $manager->getWorkspaceToolsConfigurationArray($workspace));
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
        $switchTool = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');

        return array(
            array('switchTool' => $switchTool, 'isExceptionExpected' => true),
            array('switchTool' => null, 'isExceptionExpected' => false)
        );
    }

    public function addWorkspaceToolProvider()
    {
        $switchTool = m::mock('Claroline\CoreBundle\Entity\Tool\OrderedTool');

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
                $this->om
            );
        } else {
            $stringMocked = '[';
                $stringMocked .= array_pop($mockedMethods);

            foreach ($mockedMethods as $mockedMethod) {
                $stringMocked .= ",{$mockedMethod}";
            }

            $stringMocked .= ']';

            return m::mock(
                'Claroline\CoreBundle\Manager\ToolManager' . $stringMocked,
                array(
                    $this->ed,
                    $this->utilities,
                    $this->translator,
                    $this->om
                )
            );
        }
    }
}