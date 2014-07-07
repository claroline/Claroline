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

    public function testCreateRoleFormAction()
    {
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $this->checkAccess($workspace);
        $form = $this->mock('Symfony\Component\Form\FormInterface');
        $this->formFactory->shouldReceive('create')->with(FormFactory::TYPE_WORKSPACE_ROLE)->andReturn($form);
        $formView = $this->mock('Symfony\Component\Form\FormView');
        $form->shouldReceive('createView')->andReturn($formView);
        $expectedResult = array('workspace' => $workspace, 'form' => $formView);

        $this->assertEquals($expectedResult, $this->controller->createRoleFormAction($workspace));
    }

    public function testCreateRoleAction()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspace->shouldReceive('getGuid')->andReturn('GUID');
        $workspace->shouldReceive('getId')->andReturn(1);
        $user = new \Claroline\CoreBundle\Entity\User();
        $this->checkAccess($workspace);
        $root = new \Claroline\CoreBundle\Entity\Resource\ResourceNode();
        $newRes = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $managerRole = new \Claroline\CoreBundle\Entity\Role();
        $newRole = new \Claroline\CoreBundle\Entity\Role();
        $dirType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $fileType = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $dirType->shouldReceive('getName')->andReturn('directory');
        $fileType->shouldReceive('getName')->andReturn('file');
        $form = $this->mock('Symfony\Component\Form\FormInterface');
        $this->formFactory->shouldReceive('create')->with(FormFactory::TYPE_WORKSPACE_ROLE)->andReturn($form);
        $form->shouldReceive('handleRequest')->once()->with($this->request);
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $translationKey = $this->mock('Symfony\Component\Form\FormInterface');
        $requireDir = $this->mock('Symfony\Component\Form\FormInterface');
        $form->shouldReceive('get')->with('translationKey')->once()->andReturn($translationKey);
        $form->shouldReceive('get')->with('requireDir')->once()->andReturn($requireDir);
        $translationKey->shouldReceive('getData')->once()->andReturn('roleName');
        $requireDir->shouldReceive('getData')->once()->andReturn(true);
        $this->roleManager->shouldReceive('createWorkspaceRole')
            ->with('ROLE_WS_ROLENAME_GUID', 'roleName', $workspace)->andReturn($newRole);
        $this->resourceManager->shouldReceive('getAllResourceTypes')->andReturn(array($dirType, $fileType));
        $this->resourceManager->shouldReceive('getResourceTypeByName')->once()->andReturn($dirType);
        $this->resourceManager->shouldReceive('getWorkspaceRoot')->with($workspace)->andReturn($root);
        $this->roleManager->shouldReceive('getManagerRole')->andReturn($managerRole);
        $formView = $this->mock('Symfony\Component\Form\FormView');
        $form->shouldReceive('createView')->andReturn($formView);
        $this->router->shouldReceive('generate')->andReturn('new/route');
        $this->resourceManager->shouldReceive('createResource')
            ->with('Claroline\CoreBundle\Entity\Resource\Directory', 'roleName')->andReturn($newRes);

        $perms = array(
            'ROLE_WS_ROLENAME' => array(
                'open' => true,
                'edit' => true,
                'copy' => true,
                'delete' => true,
                'export' => true,
                'create' => array(
                    array('name' => 'directory'),
                    array('name' => 'file')
                ),
                'role' => $newRole
            ),
            'ROLE_WS_MANAGER' => array(
                'open' => true,
                'edit' => true,
                'copy' => true,
                'delete' => true,
                'export' => true,
                'create' => array(
                    array('name' => 'directory'),
                    array('name' => 'file')
                ),
                'role' => $managerRole
            )
        );

        $this->resourceManager->shouldReceive('create')->once()->with(
            $newRes,
            $dirType,
            $user,
            $workspace,
            $root,
            null,
            $perms
        );

        $this->controller->createRoleAction($workspace, $user);
    }

    public function testRemoveRoleAction()
    {
        $role = new \Claroline\CoreBundle\Entity\Role();
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();
        $this->checkAccess($workspace);
        $this->roleManager->shouldReceive('remove')->once()->with($role);
        $this->controller->removeRoleAction($workspace, $role);
    }

    public function testEditRoleFormAction()
    {
        $role = new \Claroline\CoreBundle\Entity\Role;
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace;
        $formView = $this->mock('Symfony\Component\Form\FormView');
        $form = $this->mock('Symfony\Component\Form\FormInterface');
        $this->checkAccess($workspace);
        $this->formFactory->shouldReceive('create')->once()
            ->with(FormFactory::TYPE_ROLE_TRANSLATION, array(), $role)->andReturn($form);
        $form->shouldReceive('createView')->once()->andReturn($formView);
        $expected = array(
            'workspace' => $workspace,
            'form' => $formView,
            'role' => $role
        );
        $this->assertEquals($expected, $this->controller->editRoleFormAction($role, $workspace));
    }

    public function testEditRoleAction()
    {
        $role = $this->mock('Claroline\CoreBundle\Entity\Role');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $form = $this->mock('Symfony\Component\Form\FormInterface');

        $this->security
            ->shouldReceive('isGranted')
            ->with('users', $workspace)
            ->once()
            ->andReturn(true);
        $this->formFactory
            ->shouldReceive('create')
            ->once()
            ->with(FormFactory::TYPE_ROLE_TRANSLATION, array(), $role)
            ->andReturn($form);
        $form->shouldReceive('handleRequest')->once()->with($this->request);
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $this->roleManager->shouldReceive('edit')->once()->with($role);
        $workspace->shouldReceive('getId')->once()->andReturn(2);
        $this->router
            ->shouldReceive('generate')
            ->with('claro_workspace_roles', array('workspace' => 2))
            ->once()
            ->andReturn('route');

        $this->controller->editRoleAction($role, $workspace);
    }

    public function testRemoveUserFromRoleAction()
    {
        $role = new \Claroline\CoreBundle\Entity\Role;
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace;
        $user = new \Claroline\CoreBundle\Entity\User;
        $this->checkAccess($workspace);
        $this->roleManager->shouldReceive('dissociateWorkspaceRole')->once()
            ->with($user, $workspace, $role);
        $this->controller->removeUserFromRoleAction($user, $role, $workspace);
    }

    public function testAddUsersToRoleAction()
    {
        $user = new \Claroline\CoreBundle\Entity\User;
        $role = new \Claroline\CoreBundle\Entity\Role;
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace;
        $this->roleManager->shouldReceive('associateRolesToSubjects')->with(array($user), array($role))->once();
        $this->checkAccess($workspace);
        $this->controller->addUsersToRolesAction(array($user), array($role), $workspace);
    }

    public function testRemoveGroupFromRoleAction()
    {
        $role = new \Claroline\CoreBundle\Entity\Role;
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace;
        $group = new \Claroline\CoreBundle\Entity\Group;
        $this->checkAccess($workspace);
        $this->roleManager->shouldReceive('dissociateWorkspaceRole')->once()
            ->with($group, $workspace, $role);
        $this->controller->removeGroupFromRoleAction($group, $role, $workspace);
    }

    public function testAddGroupsToRolesAction()
    {
        $group = new \Claroline\CoreBundle\Entity\Group;
        $role = new \Claroline\CoreBundle\Entity\Role;
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace;
        $this->roleManager->shouldReceive('associateRolesToSubjects')->with(array($group), array($role))->once();
        $this->checkAccess($workspace);
        $this->controller->addGroupsToRolesAction(array($group), array($role), $workspace);
    }

    /**
     * @dataProvider userListProvider
     * @todo test the $call method parameters
     */
    public function testUsersListAction($search, $call)
    {
        $wsRole = new \Claroline\CoreBundle\Entity\Role;
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace;

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
            'order' => 'id'
        );
        $this->assertEquals(
            $expected,
            $this->controller->usersListAction($workspace, $page, $search, 50, 'id')
        );
    }

    /**
     * @dataProvider groupListProvider
     * @todo test the $call method parameters
     */
    public function testRegisteredGroupsListAction($search, $call)
    {
        $wsRole = new \Claroline\CoreBundle\Entity\Role;
        $role = new \Claroline\CoreBundle\Entity\Role;
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace;
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
            'order' => 'id'
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
            array('search' => 'zorglub', 'call' => 'getByRolesAndNameIncludingGroups')
        );
    }

    public function groupListProvider()
    {
        return array(
            array('search' => '', 'call' => 'getGroupsByRoles'),
            array('search' => 'zorglub', 'call' => 'getGroupsByRolesAndName')
        );
    }
}
