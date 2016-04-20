<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class AnnouncementControllerTest extends MockeryTestCase
{
    private $announcementManager;
    private $formFactory;
    private $pagerFactory;
    private $securityContext;
    private $dispatcher;
    private $translator;
    private $utils;
    private $workspaceManager;

    protected function setUp()
    {
        parent::setUp();
        $this->announcementManager = $this->mock('Claroline\AnnouncementBundle\Manager\AnnouncementManager');
        $this->formFactory = $this->mock('Symfony\Component\Form\FormFactoryInterface');
        $this->pagerFactory = $this->mock('Claroline\CoreBundle\Pager\PagerFactory');
        $this->securityContext = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->dispatcher = $this->mock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->translator = $this->mock('Symfony\Component\Translation\Translator');
        $this->utils = $this->mock('Claroline\CoreBundle\Library\Security\Utilities');
        $this->workspaceManager = $this->mock('Claroline\CoreBundle\Manager\WorkspaceManager');
    }

    public function testAnnouncementsListActionWithEditPerm()
    {
        $controller = $this->getController(array('checkAccess'));
        $aggregate = $this->mock('Claroline\AnnouncementBundle\Entity\AnnouncementAggregate');
        $resourceNode = new ResourceNode();
        $collection = new ResourceCollection(array($resourceNode));
        $announcementA = new Announcement();
        $announcementB = new Announcement();
        $announcements = array($announcementA, $announcementB);

        $aggregate->shouldReceive('getResourceNode')
            ->once()
            ->andReturn($resourceNode);
        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('EDIT', anInstanceOf('Claroline\CoreBundle\Library\Resource\ResourceCollection'))
            ->once()
            ->andReturn(true);
        $this->announcementManager
            ->shouldReceive('getAllAnnouncementsByAggregate')
            ->with($aggregate)
            ->once()
            ->andReturn($announcements);
        $this->pagerFactory
            ->shouldReceive('createPagerFromArray')
            ->with($announcements, 1, 5)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                '_resource' => $aggregate,
                'announcements' => 'pager',
                'resourceCollection' => $collection,
            ),
            $controller->announcementsListAction($aggregate, 1)
        );
    }

    public function testAnnouncementsListActionWithOpenPerm()
    {
        $controller = $this->getController(array('checkAccess'));
        $aggregate = $this->mock('Claroline\AnnouncementBundle\Entity\AnnouncementAggregate');
        $resourceNode = new ResourceNode();
        $collection = new ResourceCollection(array($resourceNode));
        $announcementA = new Announcement();
        $announcementB = new Announcement();
        $announcements = array($announcementA, $announcementB);

        $aggregate->shouldReceive('getResourceNode')
            ->once()
            ->andReturn($resourceNode);
        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('EDIT', anInstanceOf('Claroline\CoreBundle\Library\Resource\ResourceCollection'))
            ->once()
            ->andReturn(false);
        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('OPEN', anInstanceOf('Claroline\CoreBundle\Library\Resource\ResourceCollection'))
            ->once()
            ->andReturn(true);
        $this->announcementManager
            ->shouldReceive('getVisibleAnnouncementsByAggregate')
            ->with($aggregate)
            ->once()
            ->andReturn($announcements);
        $this->pagerFactory
            ->shouldReceive('createPagerFromArray')
            ->with($announcements, 1, 5)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                '_resource' => $aggregate,
                'announcements' => 'pager',
                'resourceCollection' => $collection,
            ),
            $controller->announcementsListAction($aggregate, 1)
        );
    }

    public function testCreateFormAction()
    {
        $controller = $this->getController(array('checkAccess'));
        $aggregate = new AnnouncementAggregate();
        $announcement = new Announcement();
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('EDIT', anInstanceOf('Claroline\CoreBundle\Library\Resource\ResourceCollection'))
            ->once()
            ->andReturn(true);
        $this->formFactory
            ->shouldReceive('create')
            ->with(
                anInstanceOf('Claroline\AnnouncementBundle\Form\AnnouncementType'),
                anInstanceOf('Claroline\AnnouncementBundle\Entity\Announcement')
            )
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(
            array(
                'form' => 'view',
                'type' => 'create',
                '_resource' => $aggregate,
            ),
            $controller->createFormAction($aggregate)
        );
    }

    public function testCreateAction()
    {
        $this->markTestSkipped('Will be tested later');
    }

    public function testAnnouncementEditFormAction()
    {
        $controller = $this->getController(array('checkAccess'));
        $announcement = $this->mock('Claroline\AnnouncementBundle\Entity\Announcement');
        $aggregate = new AnnouncementAggregate();
        $form = $this->mock('Symfony\Component\Form\Form');

        $announcement->shouldReceive('getAggregate')
            ->once()
            ->andReturn($aggregate);
        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('EDIT', anInstanceOf('Claroline\CoreBundle\Library\Resource\ResourceCollection'))
            ->once()
            ->andReturn(true);
        $this->formFactory
            ->shouldReceive('create')
            ->with(anInstanceOf('Claroline\AnnouncementBundle\Form\AnnouncementType'), $announcement)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')
            ->once()
            ->andReturn('view');

        $this->assertEquals(
            array(
                'form' => 'view',
                'type' => 'edit',
                'announcement' => $announcement,
                '_resource' => $aggregate,
            ),
            $controller->announcementEditFormAction($announcement)
        );
    }

    public function testAnnouncementEditAction()
    {
        $this->markTestSkipped('Will be tested later');
    }

    public function testAnnouncementDeleteAction()
    {
        $this->markTestSkipped('Event dispatching must be mocked');

        $controller = $this->getController(array('checkAccess'));
        $announcement = $this->mock('Claroline\AnnouncementBundle\Entity\Announcement');
        $aggregate = new AnnouncementAggregate();

        $announcement->shouldReceive('getAggregate')
            ->once()
            ->andReturn($aggregate);
        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('EDIT', anInstanceOf('Claroline\CoreBundle\Library\Resource\ResourceCollection'))
            ->once()
            ->andReturn(true);
        $this->announcementManager
            ->shouldReceive('deleteAnnouncement')
            ->with($announcement)
            ->once();

        $this->assertEquals(
            new Response(204),
            $controller->announcementDeleteAction($announcement)
        );
    }

    public function testAnnouncementsWorkspaceWidgetPagerAction()
    {
        $controller = $this->getController(array('checkAccess'));
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $roleA = new Role();
        $roleB = new Role();
        $roles = array($roleA, $roleB);
        $announcementA = new Announcement();
        $announcementB = new Announcement();
        $datas = array(
            array('announcement' => $announcementA),
            array('announcement' => $announcementB),
        );

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $this->utils
            ->shouldReceive('getRoles')
            ->with($token)
            ->once()
            ->andReturn($roles);
        $this->announcementManager
            ->shouldReceive('getVisibleAnnouncementsByWorkspace')
            ->with($workspace, $roles)
            ->once()
            ->andReturn($datas);
        $this->pagerFactory
            ->shouldReceive('createPagerFromArray')
            ->with($datas, 1, 5)
            ->once()
            ->andReturn('pager');
        $workspace
            ->shouldReceive('getId')
            ->once()
            ->andReturn(1);

        $this->assertEquals(
            array(
                'datas' => 'pager',
                'widgetType' => 'workspace',
                'workspaceId' => 1,
            ),
            $controller->announcementsWorkspaceWidgetPagerAction($workspace, 1)
        );
    }

    public function testAnnouncementsDesktopWidgetPagerAction()
    {
        $controller = $this->getController(array('checkAccess'));
        $workspaceA = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaceB = $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');
        $workspaces = array($workspaceA, $workspaceB);
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $roleA = new Role();
        $roleB = new Role();
        $roles = array($roleA, $roleB);
        $announcementA = new Announcement();
        $announcementB = new Announcement();
        $datas = array(
            array('announcement' => $announcementA),
            array('announcement' => $announcementB),
        );

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $this->utils
            ->shouldReceive('getRoles')
            ->with($token)
            ->once()
            ->andReturn($roles);
        $this->workspaceManager
            ->shouldReceive('getWorkspacesByRoles')
            ->with($roles)
            ->once()
            ->andReturn($workspaces);
        $this->announcementManager
            ->shouldReceive('getVisibleAnnouncementsByWorkspaces')
            ->with($workspaces, $roles)
            ->once()
            ->andReturn($datas);
        $this->pagerFactory
            ->shouldReceive('createPagerFromArray')
            ->with($datas, 1, 5)
            ->once()
            ->andReturn('pager');

        $this->assertEquals(
            array(
                'datas' => 'pager',
                'widgetType' => 'desktop',
            ),
            $controller->announcementsDesktopWidgetPagerAction(1)
        );
    }

    private function getController(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {
            return new AnnouncementController(
                $this->announcementManager,
                $this->formFactory,
                $this->pagerFactory,
                $this->securityContext,
                $this->dispatcher,
                $this->translator,
                $this->utils,
                $this->workspaceManager
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\AnnouncementBundle\Controller\AnnouncementController'.$stringMocked,
            array(
                $this->announcementManager,
                $this->formFactory,
                $this->pagerFactory,
                $this->securityContext,
                $this->dispatcher,
                $this->translator,
                $this->utils,
                $this->workspaceManager,
            )
        );
    }
}
