<?php

namespace Claroline\CoreBundle\Controller\Tool;

use \Mockery as m;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Claroline\CoreBundle\Form\Factory\FormFactory;

class WorkspaceParametersControllerTest extends MockeryTestCase
{
    private $workspaceManager;
    private $roleManager;
    private $resourceManager;
    private $security;
    private $eventDispatcher;
    private $formFactory;
    private $router;
    private $request;
    private $controller;

    public function setUp()
    {
        parent::setUp();

        $this->workspaceManager = m::mock('Claroline\CoreBundle\Manager\WorkspaceManager');
        $this->roleManager = m::mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->resourceManager = m::mock('Claroline\CoreBundle\Manager\ResourceManager');
        $this->security = m::mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->eventDispatcher = m::mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->formFactory = m::mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->router = m::mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->request = m::mock('Symfony\Component\HttpFoundation\Request');

        $this->controller = new WorkspaceParametersController(
            $this->workspaceManager,
            $this->roleManager,
            $this->resourceManager,
            $this->security,
            $this->eventDispatcher,
            $this->formFactory,
            $this->router,
            $this->request
        );
    }

    public function testConfigureRolePageAction()
    {
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace();
        $role = new \Claroline\CoreBundle\Entity\Role();
        $roles = array($role);
        $this->checkAccess($workspace);
        $this->roleManager->shouldReceive('getRolesByWorkspace')->once()->with($workspace)->andReturn($roles);

        $expectedResult = array('workspace' => $workspace, 'roles' => $roles);
        $this->assertEquals($expectedResult, $this->controller->configureRolePageAction($workspace));
    }

    public function testCreateRoleFormAction()
    {
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace();
        $this->checkAccess($workspace);
        $form = m::mock('Symfony\Component\Form\FormInterface');
        $this->formFactory->shouldReceive('create')->with(FormFactory::TYPE_WORKSPACE_ROLE)->andReturn($form);
        $formView = m::mock('Symfony\Component\Form\FormView');
        $form->shouldReceive('createView')->andReturn($formView);
        $expectedResult = array('workspace' => $workspace, 'form' => $formView);

        $this->assertEquals($expectedResult, $this->controller->createRoleFormAction($workspace));
    }

    public function testCreateRoleAction()
    {
        $workspace = m::mock('Claroline\CoreBundle\Entity\Workspace\SimpleWorkspace');
        $workspace->shouldReceive('getGuid')->andReturn('GUID');
        $user = new \Claroline\CoreBundle\Entity\User();
        $this->checkAccess($workspace);
        $root = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $newRes = new \Claroline\CoreBundle\Entity\Resource\Directory();
        $managerRole = new \Claroline\CoreBundle\Entity\Role();
        $newRole = new \Claroline\CoreBundle\Entity\Role();
        $dirType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $fileType = m::mock('Claroline\CoreBundle\Entity\Resource\ResourceType');
        $dirType->shouldReceive('getName')->andReturn('directory');
        $fileType->shouldReceive('getName')->andReturn('file');
        $form = m::mock('Symfony\Component\Form\FormInterface');
        $this->formFactory->shouldReceive('create')->with(FormFactory::TYPE_WORKSPACE_ROLE)->andReturn($form);
        $form->shouldReceive('handleRequest')->once()->with($this->request);
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $translationKey = m::mock('Symfony\Component\Form\FormInterface');
        $requireDir = m::mock('Symfony\Component\Form\FormInterface');
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
        $formView = m::mock('Symfony\Component\Form\FormView');
        $form->shouldReceive('createView')->andReturn($formView);
        $this->resourceManager->shouldReceive('createResource')
            ->with('Claroline\CoreBundle\Entity\Resource\Directory', 'roleName')->andReturn($newRes);

        $perms = array(
            'ROLE_WS_ROLENAME' => array(
                'canOpen' => true,
                'canEdit' => true,
                'canCopy' => true,
                'canDelete' => true,
                'canExport' => true,
                'canCreate' => array(
                    array('name' => 'directory'),
                    array('name' => 'file')
                ),
                'role' => $newRole
            ),
            'ROLE_WS_MANAGER' => array(
                'canOpen' => true,
                'canEdit' => true,
                'canCopy' => true,
                'canDelete' => true,
                'canExport' => true,
                'canCreate' => array(
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

        $expectedResult = array('workspace' => $workspace, 'form' => $formView);
        $this->assertEquals($expectedResult, $this->controller->createRoleAction($workspace, $user));
    }

    private function checkAccess(\Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace $workspace)
    {
        $this->security->shouldReceive('isGranted')->with('parameters', $workspace)->once()->andReturn(true);
    }
}


