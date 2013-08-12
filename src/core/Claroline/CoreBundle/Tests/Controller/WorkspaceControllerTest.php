<?php

namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class WorkspaceControllerTest extends MockeryTestCase
{
    private $resourceManager;
    private $roleManager;
    private $tagManager;
    private $toolManager;
    private $workspaceManager;
    private $eventDispatcher;
    private $security;
    private $router;
    private $utils;
    private $formFactory;

    protected function setUp()
    {
        parent::setUp();
        $this->resourceManager = $this->mock('Claroline\CoreBundle\Manager\ResourceManager');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->tagManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceTagManager');
        $this->toolManager = $this->mock('Claroline\CoreBundle\Manager\ToolManager');
        $this->workspaceManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceManager');
        $this->eventDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->security = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->router = $this->mock('Symfony\Component\Routing\Generator\UrlGeneratorInterface');
        $this->utils = $this->mock('Claroline\CoreBundle\Library\Security\Utilities');
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
    }

    public function testListAction()
    {
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
            ->with(false)
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
            $this->getController()->listAction()
        );
    }

    public function testListWorkspacesByUserAction()
    {
        $controller = $this->getController(array('assertIsGranted', 'isTagDisplayable'));
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $roles = array('ROLE_A', 'ROLE_B');
        $workspaces = array('worksapce_1', 'workspace_2');
        $tagA = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTag');
        $tagB = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTag');
        $tagC = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTag');
        $tagD = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTag');
        $tags = array($tagA, $tagB, $tagC);
        $allTags = array($tagA, $tagB, $tagC, $tagD);
        $relTagWorkspace = array(
            array('tag_id' => 1, 'rel_ws_tag' => 'relation_1'),
            array('tag_id' => 1, 'rel_ws_tag' => 'relation_2'),
            array('tag_id' => 2, 'rel_ws_tag' => 'relation_3'),
            array('tag_id' => 3, 'rel_ws_tag' => 'relation_4')
        );
        $tagHierarchyA = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy');
        $tagHierarchyB = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy');
        $tagHierarchyC = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy');
        $tagHierarchyD = $this->mock('Claroline\CoreBundle\Entity\Workspace\WorkspaceTagHierarchy');
        $tagsHierarchy = array($tagHierarchyA, $tagHierarchyB, $tagHierarchyC, $tagHierarchyD);
        $rootTags = array($tagA);

        $this->security->shouldReceive('isGranted')->with('ROLE_USER', null)->once()->andReturn(true);
        $this->security->shouldReceive('getToken')->once()->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $this->utils->shouldReceive('getRoles')->with($token)->once()->andReturn($roles);
        $this->workspaceManager
            ->shouldReceive('getWorkspacesByRoles')
            ->with($roles)
            ->once()
            ->andReturn($workspaces);
        $this->tagManager
            ->shouldReceive('getNonEmptyTagsByUser')
            ->with($user)
            ->once()
            ->andReturn($tags);
        $this->tagManager
            ->shouldReceive('getTagRelationsByUser')
            ->with($user)
            ->once()
            ->andReturn($relTagWorkspace);
        $this->tagManager
            ->shouldReceive('getAllHierarchiesByUser')
            ->with($user)
            ->once()
            ->andReturn($tagsHierarchy);
        $this->tagManager
            ->shouldReceive('getRootTags')
            ->with($user)
            ->once()
            ->andReturn($rootTags);
        $tagHierarchyA->shouldReceive('getLevel')->once()->andReturn(0);
        $tagHierarchyB->shouldReceive('getLevel')->once()->andReturn(0);
        $tagHierarchyC->shouldReceive('getLevel')->once()->andReturn(0);
        $tagHierarchyD->shouldReceive('getLevel')->once()->andReturn(1);
        $tagHierarchyD->shouldReceive('getParent')->times(3)->andReturn($tagA);
        $tagA->shouldReceive('getId')->times(4)->andReturn(1);
        $tagHierarchyD->shouldReceive('getTag')->once()->andReturn($tagB);
        $this->tagManager
            ->shouldReceive('getTagsByUser')
            ->with($user)
            ->once()
            ->andReturn($allTags);
        $tagB->shouldReceive('getId')->once()->andReturn(2);
        $tagC->shouldReceive('getId')->once()->andReturn(3);
        $tagD->shouldReceive('getId')->once()->andReturn(4);

        $controller->listWorkspacesByUserAction();
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
                'user' => $user,
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
        $this->markTestSkipped('Cannot test because of some method calls');
    }

    public function testDeleteAction()
    {
        $controller = $this->getController(array('assertIsGranted'));
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $this->security
            ->shouldReceive('isGranted')
            ->with('DELETE', $workspace)
            ->once()
            ->andReturn(true);
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with('log', 'Log\LogWorkspaceDelete', array($workspace))
            ->once();
        $this->workspaceManager
            ->shouldReceive('deleteWorkspace')
            ->with($workspace)
            ->once();

        $this->assertEquals(new Response('success', 204), $controller->deleteAction($workspace));
    }

    public function testRenderToolListActionWithBreadcrumbs()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $resource = $this->mock('Claroline\CoreBundle\Entity\Resource\ResourceNode');
        $breadcrumbs = array(0, 0);
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $roles = array('ROLE_1', 'ROLE_2');
        $orderedTools = array('ordered_tool_1', 'ordered_tool_2');

        $this->resourceManager
            ->shouldReceive('getResource')
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
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
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
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $event = $this->mock('Claroline\CoreBundle\Event\Event\DisplayToolEvent');

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
        $event->shouldReceive('getContent')->once()->andReturn('content');

        $this->assertEquals(
            new Response('content'),
            $controller->openToolAction($toolName, $workspace)
        );
    }

    public function testWidgetsAction()
    {
        $this->markTestSkipped('Maybe after refactoring of widget manager');
    }

    public function testOpenActionAdmin()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
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

        $this->assertEquals(
            new RedirectResponse('route'),
            $this->getController()->openAction($workspace)
        );
    }

    public function testOpenActionUser()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
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

        $this->assertEquals(
            new RedirectResponse('route'),
            $this->getController()->openAction($workspace)
        );
    }

    public function testOpenActionAnonymous()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
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

        $this->assertEquals(
            new RedirectResponse('route'),
            $this->getController()->openAction($workspace)
        );
    }

    public function testFindRoleByWorkspaceCodeAction()
    {
        $roleA = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roleB = $this->mock('Claroline\CoreBundle\Entity\Role');
        $roles = array($roleA, $roleB);
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $workspaceB = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
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
            new JsonResponse($arWorkspace),
            $this->getController()->findRoleByWorkspaceCodeAction('search')
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

    private function getController(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {

            return new WorkspaceController(
                $this->workspaceManager,
                $this->resourceManager,
                $this->roleManager,
                $this->tagManager,
                $this->toolManager,
                $this->eventDispatcher,
                $this->security,
                $this->router,
                $this->utils,
                $this->formFactory
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
                $this->workspaceManager,
                $this->resourceManager,
                $this->roleManager,
                $this->tagManager,
                $this->toolManager,
                $this->eventDispatcher,
                $this->security,
                $this->router,
                $this->utils,
                $this->formFactory
            )
        );
    }
}