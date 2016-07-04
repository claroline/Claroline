<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Tool;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Form\Factory\FormFactory;

class RolesControllerTest extends MockeryTestCase
{
    private $roleManager;
    private $userManager;
    private $groupManager;
    private $resourceManager;
    private $security;
    private $formFactory;
    private $router;
    private $request;
    private $controller;

    public function setUp()
    {
        parent::setUp();

        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->userManager = $this->mock('Claroline\CoreBundle\Manager\UserManager');
        $this->groupManager = $this->mock('Claroline\CoreBundle\Manager\GroupManager');
        $this->resourceManager = $this->mock('Claroline\CoreBundle\Manager\ResourceManager');
        $this->security = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->router = $this->mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');

        $this->controller = new RolesController(
            $this->roleManager,
            $this->userManager,
            $this->groupManager,
            $this->resourceManager,
            $this->security,
            $this->formFactory,
            $this->router,
            $this->request
        );
    }

    public function testConfigureRolePageAction()
    {
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $role = new \Claroline\CoreBundle\Entity\Role();
        $roles = array($role);
        $this->checkAccess($workspace);
        $this->roleManager->shouldReceive('getRolesByWorkspace')->once()->with($workspace)->andReturn($roles);

        $expectedResult = array('workspace' => $workspace, 'roles' => $roles);
        $this->assertEquals($expectedResult, $this->controller->configureRolePageAction($workspace));
    }

    public function testRemoveRoleAction()
    {
        $role = new \Claroline\CoreBundle\Entity\Role();
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $this->checkAccess($workspace);
        $this->roleManager->shouldReceive('remove')->once()->with($role);
        $this->controller->removeRoleAction($workspace, $role);
    }

    public function testRemoveUserFromRoleAction()
    {
        $role = new \Claroline\CoreBundle\Entity\Role();
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $user = new \Claroline\CoreBundle\Entity\User();
        $this->checkAccess($workspace);
        $this->roleManager->shouldReceive('dissociateWorkspaceRole')->once()
            ->with($user, $workspace, $role);
        $this->controller->removeUserFromRoleAction($user, $role, $workspace);
    }

    public function testAddUsersToRoleAction()
    {
        $user = new \Claroline\CoreBundle\Entity\User();
        $role = new \Claroline\CoreBundle\Entity\Role();
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $this->roleManager->shouldReceive('associateRolesToSubjects')->with(array($user), array($role))->once();
        $this->checkAccess($workspace);
        $this->controller->addUsersToRolesAction(array($user), array($role), $workspace);
    }

    public function testRemoveGroupFromRoleAction()
    {
        $role = new \Claroline\CoreBundle\Entity\Role();
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $group = new \Claroline\CoreBundle\Entity\Group();
        $this->checkAccess($workspace);
        $this->roleManager->shouldReceive('dissociateWorkspaceRole')->once()
            ->with($group, $workspace, $role);
        $this->controller->removeGroupFromRoleAction($group, $role, $workspace);
    }

    public function testAddGroupsToRolesAction()
    {
        $group = new \Claroline\CoreBundle\Entity\Group();
        $role = new \Claroline\CoreBundle\Entity\Role();
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $this->roleManager->shouldReceive('associateRolesToSubjects')->with(array($group), array($role))->once();
        $this->checkAccess($workspace);
        $this->controller->addGroupsToRolesAction(array($group), array($role), $workspace);
    }

    /**
     * @dataProvider userListProvider
     *
     * @todo test the $call method parameters
     */
    public function testUsersListAction($search, $call)
    {
        $wsRole = new \Claroline\CoreBundle\Entity\Role();
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();

        $this->security
            ->shouldReceive('isGranted')
            ->with('users', $workspace)
            ->once()
            ->andReturn(true);

        $wsRoles = array($wsRole);
        $page = 1;
        $this->roleManager->shouldReceive('getRolesByWorkspace')->once()->with($workspace)->andReturn($wsRoles);
        $this->userManager->shouldReceive($call)->once()->andReturn('pager');
        $expected = array(
            'workspace' => $workspace,
            'pager' => 'pager',
            'search' => $search,
            'wsRoles' => $wsRoles,
            'max' => 50,
            'order' => 'id',
        );
        $this->assertEquals(
            $expected,
            $this->controller->usersListAction($workspace, $page, $search, 50, 'id')
        );
    }

    /**
     * @dataProvider groupListProvider
     *
     * @todo test the $call method parameters
     */
    public function testRegisteredGroupsListAction($search, $call)
    {
        $wsRole = new \Claroline\CoreBundle\Entity\Role();
        $role = new \Claroline\CoreBundle\Entity\Role();
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $wsRoles = array($wsRole);
        $roles = array($role);
        $page = 1;

        $this->security
            ->shouldReceive('isGranted')
            ->with('users', $workspace)
            ->once()
            ->andReturn(true);

        $this->roleManager->shouldReceive('getRolesByWorkspace')->once()->with($workspace)->andReturn($wsRoles);
        $this->groupManager->shouldReceive($call)->once()->andReturn('pager');
        $expected = array(
            'workspace' => $workspace,
            'pager' => 'pager',
            'search' => $search,
            'wsRoles' => $wsRoles,
            'max' => 50,
            'order' => 'id',
        );
        $this->assertEquals(
            $expected,
            $this->controller->groupsListAction($workspace, $page, $search, 50, 'id')
        );
    }

    private function checkAccess(\Claroline\CoreBundle\Entity\Workspace\Workspace $workspace)
    {
        $this->security->shouldReceive('isGranted')->with('users', $workspace)->once()->andReturn(true);
    }

    public function userListProvider()
    {
        return array(
            array('search' => '', 'call' => 'getByRolesIncludingGroups'),
            array('search' => 'zorglub', 'call' => 'getByRolesAndNameIncludingGroups'),
        );
    }

    public function groupListProvider()
    {
        return array(
            array('search' => '', 'call' => 'getGroupsByRoles'),
            array('search' => 'zorglub', 'call' => 'getGroupsByRolesAndName'),
        );
    }
}
