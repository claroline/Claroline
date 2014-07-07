<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class WorkspaceControllerTest extends MockeryTestCase
{
    private $homeTabManager;
    private $resourceManager;
    private $roleManager;
    private $userManager;
    private $tagManager;
    private $toolManager;
    private $workspaceManager;
    private $eventDispatcher;
    private $security;
    private $router;
    private $utils;
    private $formFactory;
    private $tokenUpdater;
    private $widgetManager;
    private $request;
    private $templateDir;

    protected function setUp()
    {
        parent::setUp();
        $this->homeTabManager = $this->mock('Claroline\CoreBundle\Manager\HomeTabManager');
        $this->resourceManager = $this->mock('Claroline\CoreBundle\Manager\ResourceManager');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->userManager = $this->mock('Claroline\CoreBundle\Manager\UserManager');
        $this->tagManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceTagManager');
        $this->toolManager = $this->mock('Claroline\CoreBundle\Manager\ToolManager');
        $this->workspaceManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceManager');
        $this->eventDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->security = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->router = $this->mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->utils = $this->mock('Claroline\CoreBundle\Library\Security\Utilities');
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->tokenUpdater = $this->mock('Claroline\CoreBundle\Library\Security\TokenUpdater');
        $this->widgetManager = $this->mock('Claroline\CoreBundle\Manager\WidgetManager');
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->templateDir = 'path/to/templates';
    }

    public function testListAction()
    {
        $user = new User();

        $datas = array(
            'workspaces' => 'workspaces',
            'tags' => 'tags',
            'tagWorkspaces' => 'tagWorkspaces',
            'hierarchy' => 'hierarchy',
            'rootTags' => 'rootTags',
            'displayable' => 'displayable',
            'workspaceRoles' => 'workspaceRoles'
        );

        $this->tagManager
            ->shouldReceive('getDatasForWorkspaceList')
            ->with(false, $user)
            ->once()
            ->andReturn($datas);

        $this->assertEquals(
            array(
                'workspaces' => $datas['workspaces'],
                'tags' => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy' => $datas['hierarchy'],
                'rootTags' => $datas['rootTags'],
                'displayable' => $datas['displayable'],
                'workspaceRoles' => $datas['workspaceRoles']
            ),
            $this->getController()->listAction($user)
        );
    }

    public function testListWorkspacesByUserAction()
    {
        $controller = $this->getController(array('assertIsGranted'));
        $workspace = new \Claroline\CoreBundle\Entity\Workspace\Workspace();

        $datas = array(
            'workspaces' => array($workspace),
            'tags' => 'tags',
            'tagWorkspaces' => 'tagWorkspaces',
            'hierarchy' => 'hierarchy',
            'rootTags' => 'rootTags',
            'displayable' => 'displayable'
        );
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $roles = array('ROLE_A', 'ROLE_B');
        $favourites = array();

        $this->security
            ->shouldReceive('isGranted')
            ->with('ROLE_USER', null)
            ->once()
            ->andReturn(true);
        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $this->utils->shouldReceive('getRoles')->with($token)->once()->andReturn($roles);
        $this->tagManager
            ->shouldReceive('getDatasForWorkspaceListByUser')
            ->with($user, $roles)
            ->once()
            ->andReturn($datas);
        $this->workspaceManager->shouldReceive('getFavouriteWorkspacesByUser')->once()->with($user)
            ->andReturn($favourites);

        $this->assertEquals(
            array(
                'user' => $user,
                'workspaces' => array($workspace),
                'tags' => 'tags',
                'tagWorkspaces' => 'tagWorkspaces',
                'hierarchy' => 'hierarchy',
                'rootTags' => 'rootTags',
                'displayable' => 'displayable',
                'favourites' => array()
            ),
            $controller->listWorkspacesByUserAction()
        );
    }

    public function testListWorkspacesWithSelfRegistrationAction()
    {
        $controller = $this->getController(array('assertIsGranted'));
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $datas = array(
            'workspaces' => 'workspaces',
            'tags' => 'tags',
            'tagWorkspaces' => 'tagWorkspaces',
            'hierarchy' => 'hierarchy',
            'rootTags' => 'rootTags',
            'displayable' => 'displayable'
        );

        $this->security->shouldReceive('isGranted')->with('ROLE_USER', null)->once()->andReturn(true);
        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $this->tagManager
            ->shouldReceive('getDatasForSelfRegistrationWorkspaceList')
            ->once()
            ->andReturn($datas);

        $this->assertEquals(
            array(
                'workspaces' => $datas['workspaces'],
                'tags' => $datas['tags'],
                'tagWorkspaces' => $datas['tagWorkspaces'],
                'hierarchy' => $datas['hierarchy'],
                'rootTags' => $datas['rootTags'],
                'displayable' => $datas['displayable']
            ),
            $controller->listWorkspacesWithSelfRegistrationAction()
        );
    }

    public function testCreationFormAction()
    {
        $controller = $this->getController(array('assertIsGranted'));
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->security
            ->shouldReceive('isGranted')
            ->with('ROLE_WS_CREATOR', null)
            ->once()
            ->andReturn(true);
        $this->formFactory
            ->shouldReceive('create')
            ->with(FormFactory::TYPE_WORKSPACE)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')->once()->andReturn('view');

        $this->assertEquals(array('form' => 'view'), $controller->creationFormAction());
    }

    public function testCreateAction()
    {
        $this->markTestSkipped();
        $controller = $this->getController(array('assertIsGranted'));
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->security
            ->shouldReceive('isGranted')
            ->with('ROLE_WS_CREATOR', null)
            ->once()
            ->andReturn(true);
        $this->formFactory
            ->shouldReceive('create')
            ->with(FormFactory::TYPE_WORKSPACE)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')->once()->with($this->request);
        $form->shouldReceive('isValid')->once()->andReturn(true);

        $controller->createAction();

    }

    public function testDeleteAction()
    {
        $controller = $this->getController(array('assertIsGranted'));
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');

        $this->security
            ->shouldReceive('isGranted')
            ->with('DELETE', $workspace)
            ->once()
            ->andReturn(true);
        $this->security
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with('log', 'Log\LogWorkspaceDelete', array($workspace))
            ->once();
        $this->workspaceManager
            ->shouldReceive('deleteWorkspace')
            ->with($workspace)
            ->once();
        $this->tokenUpdater
            ->shouldReceive('cancelUsurpation')
            ->once()
            ->with($token);
        $response = new Response('success', 204);
        $this->assertEquals($response->getStatusCode(), $controller->deleteAction($workspace)->getStatusCode());
    }

    public function testRenderToolListActionWithBreadcrumbs()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $breadcrumbs = array(0, 0);
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $roles = array('ROLE_1', 'ROLE_2');
        $orderedTools = array('ordered_tool_1', 'ordered_tool_2');

        $this->resourceManager
            ->shouldReceive('getNode')
            ->with(0)
            ->once()
            ->andReturn($resource);
        $resource->shouldReceive('getWorkspace')
            ->once()
            ->andReturn($workspaceA);
        $this->security
            ->shouldReceive('isGranted')
            ->with('OPEN', $workspaceA)
            ->once()
            ->andReturn(true);
        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $this->utils->shouldReceive('getRoles')->with($token)->once()->andReturn($roles);
        $this->toolManager
            ->shouldReceive('getOrderedToolsByWorkspaceAndRoles')
            ->with($workspaceA, $roles)
            ->once()
            ->andReturn($orderedTools);

        $this->assertEquals(
            array('orderedTools' => $orderedTools, 'workspace' => $workspaceA),
            $this->getController()->renderToolListAction($workspace, $breadcrumbs)
        );
    }

    public function testRenderToolListActionWithoutBreadcrumbs()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $roles = array('ROLE_1', 'ROLE_2');
        $orderedTools = array('ordered_tool_1', 'ordered_tool_2');

        $this->security
            ->shouldReceive('isGranted')
            ->with('OPEN', $workspace)
            ->once()
            ->andReturn(true);
        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $this->utils->shouldReceive('getRoles')->with($token)->once()->andReturn($roles);
        $this->toolManager
            ->shouldReceive('getOrderedToolsByWorkspaceAndRoles')
            ->with($workspace, $roles)
            ->once()
            ->andReturn($orderedTools);

        $this->assertEquals(
            array('orderedTools' => $orderedTools, 'workspace' => $workspace),
            $this->getController()->renderToolListAction($workspace, null)
        );
    }

    public function testOpenToolAction()
    {
        $controller = $this->getController(array('assertIsGranted'));
        $toolName = 'tool_name';
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $event = $this->mock('Claroline\CoreBundle\Event\DisplayToolEvent');

        $this->security
            ->shouldReceive('isGranted')
            ->with($toolName, $workspace)
            ->once()
            ->andReturn(true);
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with(
                'open_tool_workspace_' . $toolName,
                'DisplayTool',
                array($workspace)
            )
            ->once()
            ->andReturn($event);
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with(
                'log',
                'Log\LogWorkspaceToolRead',
                array($workspace, $toolName)
            )
            ->once();
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with(
                'log',
                'Log\LogWorkspaceEnter',
                array($workspace)
            )
            ->once();
        $event->shouldReceive('getContent')->once()->andReturn('content');

        $this->assertEquals('content', $controller->openToolAction($toolName, $workspace)->getContent());
    }

    public function testOpenActionAdmin()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $admin = $this->mock('Claroline\CoreBundle\Entity\User');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roles = array($roleA, $roleB);
        $userRoles = array('ROLE_A', 'ROLE_C');
        $openedTool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');

        $this->security->shouldReceive('getToken')->times(4)->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn('admin');
        $this->roleManager
            ->shouldReceive('getRolesByWorkspace')
            ->with($workspace)
            ->once()
            ->andReturn($roles);
        $token->shouldReceive('getUser')->times(3)->andReturn($admin);
        $admin->shouldReceive('getRoles')->times(2)->andReturn($userRoles);
        $roleA->shouldReceive('getName')->times(2)->andReturn('ROLE_A');
        $roleB->shouldReceive('getName')->times(2)->andReturn('ROLE_B');
        $admin->shouldReceive('hasRole')->with('ROLE_ADMIN')->once()->andReturn(true);
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn($openedTool);
        $workspace->shouldReceive('getId')->once()->andReturn(1);
        $openedTool->shouldReceive('getName')->once()->andReturn('tool_name');
        $this->router
            ->shouldReceive('generate')
            ->with(
                'claro_workspace_open_tool',
                array('workspaceId' => 1, 'toolName' => 'tool_name')
            )
            ->once()
            ->andReturn('route');

        $response = $this->getController()->openAction($workspace);
        $this->assertEquals('route', $response->getTargetUrl());
    }

    public function testOpenActionUser()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roles = array($roleA, $roleB);
        $userRoles = array('ROLE_A', 'ROLE_C');
        $openedTool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');

        $this->security->shouldReceive('getToken')->times(4)->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn('user');
        $this->roleManager
            ->shouldReceive('getRolesByWorkspace')
            ->with($workspace)
            ->once()
            ->andReturn($roles);
        $token->shouldReceive('getUser')->times(3)->andReturn($user);
        $user->shouldReceive('getRoles')->times(2)->andReturn($userRoles);
        $roleA->shouldReceive('getName')->times(2)->andReturn('ROLE_A');
        $roleB->shouldReceive('getName')->times(2)->andReturn('ROLE_B');
        $user->shouldReceive('hasRole')->with('ROLE_ADMIN')->once()->andReturn(false);
        $this->toolManager
            ->shouldReceive('getDisplayedByRolesAndWorkspace')
            ->with(array('ROLE_A'), $workspace)
            ->once()
            ->andReturn(array($openedTool));
        $workspace->shouldReceive('getId')->once()->andReturn(1);
        $openedTool->shouldReceive('getName')->once()->andReturn('tool_name');
        $this->router
            ->shouldReceive('generate')
            ->with(
                'claro_workspace_open_tool',
                array('workspaceId' => 1, 'toolName' => 'tool_name')
            )
            ->once()
            ->andReturn('route');

        $response = $this->getController()->openAction($workspace);
        $this->assertEquals('route', $response->getTargetUrl());
    }

    public function testOpenActionAnonymous()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $openedTool = $this->mock('Claroline\CoreBundle\Entity\Tool\Tool');

        $this->security->shouldReceive('getToken')->times(1)->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn('anon.');
        $this->toolManager
            ->shouldReceive('getDisplayedByRolesAndWorkspace')
            ->with(array('ROLE_ANONYMOUS'), $workspace)
            ->once()
            ->andReturn(array($openedTool));
        $workspace->shouldReceive('getId')->once()->andReturn(1);
        $openedTool->shouldReceive('getName')->once()->andReturn('tool_name');
        $this->router
            ->shouldReceive('generate')
            ->with(
                'claro_workspace_open_tool',
                array('workspaceId' => 1, 'toolName' => 'tool_name')
            )
            ->once()
            ->andReturn('route');

        $response = $this->getController()->openAction($workspace);
        $this->assertEquals('route', $response->getTargetUrl());
    }

    public function testFindRoleByWorkspaceCodeAction()
    {
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roles = array($roleA, $roleB);
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaceB = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $arWorkspace = array(
            'code_A' => array(
                'ROLE_A' => array(
                    'name' => 'ROLE_A',
                    'translation_key' => 'key_A',
                    'id' => 1,
                    'workspace' => 'ws_A'
                )
            ),
            'code_B' => array(
                'ROLE_B' => array(
                    'name' => 'ROLE_B',
                    'translation_key' => 'key_B',
                    'id' => 2,
                    'workspace' => 'ws_B'
                )
            )
        );

        $this->roleManager
            ->shouldReceive('getRolesBySearchOnWorkspaceAndTag')
            ->with('search')
            ->once()
            ->andReturn($roles);
        $roleA->shouldReceive('getWorkspace')->times(2)->andReturn($workspaceA);
        $workspaceA->shouldReceive('getCode')->once()->andReturn('code_A');
        $roleA->shouldReceive('getName')->times(2)->andReturn('ROLE_A');
        $roleA->shouldReceive('getTranslationKey')->once()->andReturn('key_A');
        $roleA->shouldReceive('getId')->once()->andReturn(1);
        $workspaceA->shouldReceive('getName')->once()->andReturn('ws_A');
        $roleB->shouldReceive('getWorkspace')->times(2)->andReturn($workspaceB);
        $workspaceB->shouldReceive('getCode')->once()->andReturn('code_B');
        $roleB->shouldReceive('getName')->times(2)->andReturn('ROLE_B');
        $roleB->shouldReceive('getTranslationKey')->once()->andReturn('key_B');
        $roleB->shouldReceive('getId')->once()->andReturn(2);
        $workspaceB->shouldReceive('getName')->once()->andReturn('ws_B');

        $this->assertEquals(
            json_encode($arWorkspace),
            $this->getController()->findRoleByWorkspaceCodeAction('search')->getContent()
        );
    }

    public function testWorkspaceListByTagPagerAction()
    {
        $tag = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTag');

        $this->tagManager
            ->shouldReceive('getPagerRelationByTag')
            ->with($tag, 1)
            ->once()
            ->andReturn('relations');
        $tag->shouldReceive('getId')
            ->once()
            ->andReturn(1);

        $this->assertEquals(
            array(
                'workspaceTagId' => 1,
                'relations' => 'relations'
            ),
            $this->getController()->workspaceListByTagPagerAction($tag, 1)
        );
    }

    public function testWorkspaceCompleteListPagerAction()
    {
        $this->tagManager->shouldReceive('getPagerAllWorkspaces')
            ->with(1)
            ->once()
            ->andReturn('workspaces');

        $this->assertEquals(
            array('workspaces' => 'workspaces'),
            $this->getController()->workspaceCompleteListPagerAction(1)
        );
    }

    public function testWorkspaceListByTagRegistrationPagerAction()
    {
        $tag = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTag');

        $this->tagManager
            ->shouldReceive('getPagerRelationByTag')
            ->with($tag, 1)
            ->once()
            ->andReturn('relations');
        $tag->shouldReceive('getId')
            ->once()
            ->andReturn(1);

        $this->assertEquals(
            array(
                'workspaceTagId' => 1,
                'relations' => 'relations'
            ),
            $this->getController()->workspaceListByTagRegistrationPagerAction($tag, 1)
        );
    }

    public function testWorkspaceCompleteListRegistrationPagerAction()
    {
        $this->tagManager->shouldReceive('getPagerAllWorkspaces')
            ->with(1)
            ->once()
            ->andReturn('workspaces');

        $this->assertEquals(
            array('workspaces' => 'workspaces'),
            $this->getController()->workspaceCompleteListRegistrationPagerAction(1)
        );
    }

    public function testWorkspaceSearchedListRegistrationPagerAction()
    {
        $this->workspaceManager->shouldReceive('getDisplayableWorkspacesBySearchPager')
            ->with('search', 1)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array('workspaces' => 'pager', 'search' => 'search'),
            $this->getController()->workspaceSearchedListRegistrationPagerAction('search', 1)
        );
    }

    public function testRemoveUserAction()
    {
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleC = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleD = new Role();
        $roles = array($roleA, $roleB, $roleC);

        $this->roleManager
            ->shouldReceive('getRolesByWorkspace')
            ->with($workspace)
            ->once()
            ->andReturn($roles);
        $this->roleManager
            ->shouldReceive('checkWorkspaceRoleEditionIsValid')
            ->with(array($user), $workspace, $roles)
            ->once();
        $roleA->shouldReceive('getName')
            ->once()
            ->andReturn('ROLE_A');
        $roleB->shouldReceive('getName')
            ->once()
            ->andReturn('ROLE_B');
        $roleC->shouldReceive('getName')
            ->once()
            ->andReturn('ROLE_C');
        $user->shouldReceive('hasRole')
            ->with('ROLE_A')
            ->once()
            ->andReturn(true);
        $user->shouldReceive('hasRole')
            ->with('ROLE_B')
            ->once()
            ->andReturn(false);
        $user->shouldReceive('hasRole')
            ->with('ROLE_C')
            ->once()
            ->andReturn(false);
        $this->roleManager
            ->shouldReceive('dissociateRole')
            ->with($user, $roleA)
            ->once();
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with(
                'log',
                'Log\LogRoleUnsubscribe',
                array($roleA, $user, $workspace)
            )
            ->once();
        $this->tagManager
            ->shouldReceive('deleteAllRelationsFromWorkspaceAndUser')
            ->with($workspace, $user)
            ->once();
        $user->shouldReceive('getRoles')
            ->once()
            ->andReturn(array($roleD));
        $this->security->shouldReceive('setToken')
            ->with(anInstanceOf('Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken'))
            ->once();
        $response = $this->getController()->removeUserAction($workspace, $user);
        $this->assertEquals('success', $response->getContent());
        $this->assertEquals(204, $response->getStatusCode());
    }

    private function getController(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new WorkspaceController(
                $this->homeTabManager,
                $this->workspaceManager,
                $this->resourceManager,
                $this->roleManager,
                $this->userManager,
                $this->tagManager,
                $this->toolManager,
                $this->eventDispatcher,
                $this->security,
                $this->router,
                $this->utils,
                $this->formFactory,
                $this->tokenUpdater,
                $this->widgetManager,
                $this->request,
                $this->templateDir
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Controller\WorkspaceController' . $stringMocked,
            array(
                $this->homeTabManager,
                $this->workspaceManager,
                $this->resourceManager,
                $this->roleManager,
                $this->userManager,
                $this->tagManager,
                $this->toolManager,
                $this->eventDispatcher,
                $this->security,
                $this->router,
                $this->utils,
                $this->formFactory,
                $this->tokenUpdater,
                $this->widgetManager,
                $this->request,
                $this->templateDir
            )
        );
    }
}
