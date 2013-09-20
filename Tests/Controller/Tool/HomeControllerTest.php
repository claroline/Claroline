<?php

namespace Claroline\CoreBundle\Controller\Tool;

use \Mockery as m;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class HomeControllerTest extends MockeryTestCase
{
    private $em;
    private $eventDispatcher;
    private $formFactory;
    private $homeTabManager;
    private $request;
    private $roleManager;
    private $securityContext;
    private $toolManager;
    private $widgetManager;

    protected function setUp()
    {
        parent::setUp();
        $this->em = $this->mock('Doctrine\ORM\EntityManager');
        $this->eventDispatcher = $this->mock('Claroline\CoreBundle\Event\StrictDispatcher');
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->homeTabManager = $this->mock('Claroline\CoreBundle\Manager\HomeTabManager');
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->roleManager = $this->mock('Claroline\CoreBundle\Manager\RoleManager');
        $this->securityContext = $this->mock('Symfony\Component\Security\Core\SecurityContextInterface');
        $this->toolManager = $this->mock('Claroline\CoreBundle\Manager\ToolManager');
        $this->widgetManager = $this->mock('Claroline\CoreBundle\Library\Widget\Manager');
    }

    public function testWorkspaceWidgetsPropertiesAction()
    {
        $controller = $this->getController(array('getHomeTool'));
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $configs = array('config_a', 'config_b');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('parameters', $workspace)
            ->once()
            ->andReturn(true);
        $workspace->shouldReceive('getId')->once()->andReturn(1);
        $this->widgetManager
            ->shouldReceive('generateWorkspaceDisplayConfig')
            ->with(1)
            ->once()
            ->andReturn($configs);
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');

        $this->assertEquals(
            array(
                'workspace' => $workspace,
                'configs' => $configs,
                'tool' => 'tool'
            ),
            $controller->workspaceWidgetsPropertiesAction($workspace)
        );
    }

    public function testWorkspaceInvertVisibleWidgetAction()
    {
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $widget = new Widget();
        $adminConfig = $this->mock('Claroline\CoreBundle\Entity\Widget\DisplayConfig');
        $displayConfigRepo = $this->mock('Claroline\CoreBundle\Repository\DisplayConfigRepository');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('parameters', $workspace)
            ->once()
            ->andReturn(true);
        $this->em
            ->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Widget\DisplayConfig')
            ->once()
            ->andReturn($displayConfigRepo);
        $displayConfigRepo
            ->shouldReceive('findOneBy')
            ->with(array('workspace' => $workspace, 'widget' => $widget))
            ->once()
            ->andReturn(null);
        $adminConfig->shouldReceive('isVisible')->once()->andReturn(true);
        $this->em
            ->shouldReceive('persist')
            ->with(
                anInstanceOf('Claroline\CoreBundle\Entity\Widget\DisplayConfig')
            )
            ->once();
        $this->em->shouldReceive('flush')->once();

        $response = $this->getController()->workspaceInvertVisibleWidgetAction(
            $workspace,
            $widget,
            $adminConfig
        );
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $response
        );
        $this->assertEquals(
            'success',
            $response->getContent()
        );
    }

    public function testWorkspaceConfigureWidgetActionXml()
    {
        $controller = $this->getController(array('getHomeTool', 'render'));
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $widget = $this->mock('Claroline\CoreBundle\Entity\Widget\Widget');
        $event = $this->mock('Claroline\CoreBundle\Event\ConfigureWidgetWorkspaceEvent');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('parameters', $workspace)
            ->once()
            ->andReturn(true);
        $widget->shouldReceive('getName')->once()->andReturn('widget_name');
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with(
               'widget_widget_name_configuration_workspace',
               'ConfigureWidgetWorkspace',
                array($workspace)
            )
            ->once()
            ->andReturn($event);
        $event->shouldReceive('getContent')->times(2)->andReturn('content');
        $this->request->shouldReceive('isXmlHttpRequest')->once()->andReturn(true);
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');
        $controller
            ->shouldReceive('render')
            ->with(
                'ClarolineCoreBundle:Tool\workspace\home:widgetConfigurationForm.html.twig',
                array(
                    'content' => 'content',
                    'workspace' => $workspace,
                    'tool' => 'tool'
                )
            )
            ->once()
            ->andReturn('rendering');

        $this->assertEquals(
            'rendering',
            $controller->workspaceConfigureWidgetAction($workspace, $widget)
        );
    }

    public function testWorkspaceConfigureWidgetActionNotXml()
    {
        $controller = $this->getController(array('getHomeTool', 'render'));
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $widget = $this->mock('Claroline\CoreBundle\Entity\Widget\Widget');
        $event = $this->mock('Claroline\CoreBundle\Event\ConfigureWidgetWorkspaceEvent');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('parameters', $workspace)
            ->once()
            ->andReturn(true);
        $widget->shouldReceive('getName')->once()->andReturn('widget_name');
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with(
               'widget_widget_name_configuration_workspace',
               'ConfigureWidgetWorkspace',
                array($workspace)
            )
            ->once()
            ->andReturn($event);
        $event->shouldReceive('getContent')->times(2)->andReturn('content');
        $this->request->shouldReceive('isXmlHttpRequest')->once()->andReturn(false);
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');
        $controller
            ->shouldReceive('render')
            ->with(
                'ClarolineCoreBundle:Tool\workspace\home:widgetConfiguration.html.twig',
                array(
                    'content' => 'content',
                    'workspace' => $workspace,
                    'tool' => 'tool'
                )
            )
            ->once()
            ->andReturn('rendering');

        $this->assertEquals(
            'rendering',
            $controller->workspaceConfigureWidgetAction($workspace, $widget)
        );
    }

    public function testDesktopWidgetPropertiesAction()
    {
        $controller = $this->getController(array('getHomeTool'));
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $configs = array('config_a', 'config_b');

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $user->shouldReceive('getId')->once()->andReturn(1);
        $this->widgetManager
            ->shouldReceive('generateDesktopDisplayConfig')
            ->with(1)
            ->once()
            ->andReturn($configs);
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');

        $this->assertEquals(
            array(
                'configs' => $configs,
                'user' => $user,
                'tool' => 'tool'
            ),
            $controller->desktopWidgetPropertiesAction()
        );
    }

    public function testDesktopInvertVisibleUserWidgetAction()
    {
        $widget = new Widget();
        $adminConfig = $this->mock('Claroline\CoreBundle\Entity\Widget\DisplayConfig');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $displayConfigRepo = $this->mock('Claroline\CoreBundle\Repository\DisplayConfigRepository');

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $this->em
            ->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Widget\DisplayConfig')
            ->once()
            ->andReturn($displayConfigRepo);
        $displayConfigRepo
            ->shouldReceive('findOneBy')
            ->with(array('user' => $user, 'widget' => $widget))
            ->once()
            ->andReturn(null);
        $adminConfig->shouldReceive('isVisible')->once()->andReturn(true);
        $this->em
            ->shouldReceive('persist')
            ->with(
                anInstanceOf('Claroline\CoreBundle\Entity\Widget\DisplayConfig')
            )
            ->once();
        $this->em->shouldReceive('flush')->once();

        $response = $this->getController()->desktopInvertVisibleUserWidgetAction(
            $widget,
            $adminConfig
        );
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $response
        );
        $this->assertEquals(
            'success',
            $response->getContent()
        );
    }

    public function testDesktopConfigureWidgetActionXml()
    {
        $controller = $this->getController(array('getHomeTool', 'render'));
        $widget = $this->mock('Claroline\CoreBundle\Entity\Widget\Widget');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $event = $this->mock('Claroline\CoreBundle\Event\ConfigureWidgetDesktopEvent');

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $widget->shouldReceive('getName')->once()->andReturn('widget_name');
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with(
               'widget_widget_name_configuration_desktop',
               'ConfigureWidgetDesktop',
                array($user)
            )
            ->once()
            ->andReturn($event);
        $event->shouldReceive('getContent')->times(2)->andReturn('content');
        $this->request->shouldReceive('isXmlHttpRequest')->once()->andReturn(true);
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');
        $controller
            ->shouldReceive('render')
            ->with(
                'ClarolineCoreBundle:Tool\desktop\home:widgetConfigurationForm.html.twig',
                array(
                    'content' => 'content',
                    'tool' => 'tool'
                )
            )
            ->once()
            ->andReturn('rendering');

        $this->assertEquals(
            'rendering',
            $controller->desktopConfigureWidgetAction($widget)
        );
    }

    public function testDesktopConfigureWidgetActionNotXml()
    {
        $controller = $this->getController(array('getHomeTool', 'render'));
        $widget = $this->mock('Claroline\CoreBundle\Entity\Widget\Widget');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $event = $this->mock('Claroline\CoreBundle\Event\ConfigureWidgetDesktopEvent');

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $widget->shouldReceive('getName')->once()->andReturn('widget_name');
        $this->eventDispatcher
            ->shouldReceive('dispatch')
            ->with(
               'widget_widget_name_configuration_desktop',
               'ConfigureWidgetDesktop',
                array($user)
            )
            ->once()
            ->andReturn($event);
        $event->shouldReceive('getContent')->times(2)->andReturn('content');
        $this->request->shouldReceive('isXmlHttpRequest')->once()->andReturn(false);
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');
        $controller
            ->shouldReceive('render')
            ->with(
                'ClarolineCoreBundle:Tool\desktop\home:widgetConfiguration.html.twig',
                array(
                    'content' => 'content',
                    'tool' => 'tool'
                )
            )
            ->once()
            ->andReturn('rendering');

        $this->assertEquals(
            'rendering',
            $controller->desktopConfigureWidgetAction($widget)
        );
    }

    public function testDesktopHomeTabPropertiesAction()
    {
        $controller = $this->getController(array('checkUserAccess', 'getHomeTool'));
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $homeTabA = new HomeTab();
        $homeTabB = new HomeTab();
        $homeTabC = new HomeTab();
        $homeTabD = new HomeTab();
        $homeTabConfigA = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTabConfigB = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTabConfigC = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTabConfigD = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $adminHomeTabConfigs = array($homeTabConfigA, $homeTabConfigB);
        $homeTabConfigs = array($homeTabConfigC, $homeTabConfigD);
        $adminWidgetConfigsA = array('awc_1', 'awc_2', 'awc_3');
        $userWidgetConfigsA = array('uwc_1', 'uwc_2');
        $adminWidgetConfigsB = array('awc_4', 'awc_5');
        $userWidgetConfigsB = array('uwc_3', 'uwc_4');
        $userWidgetConfigsC = array('uwc_5', 'uwc_6');
        $userWidgetConfigsD = array();
        $nbWidgets = array(
            1 => 5,
            2 => 4,
            3 => 2,
            4 => 0
        );

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('ROLE_USER')
            ->once()
            ->andReturn(true);
        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $this->homeTabManager
            ->shouldReceive('generateAdminHomeTabConfigsByUser')
            ->with($user)
            ->once()
            ->andReturn($adminHomeTabConfigs);
        $this->homeTabManager
            ->shouldReceive('getDesktopHomeTabConfigsByUser')
            ->with($user)
            ->once()
            ->andReturn($homeTabConfigs);
        $homeTabConfigA
            ->shouldReceive('getHomeTab')
            ->times(2)
            ->andReturn($homeTabA);
        $this->homeTabManager
            ->shouldReceive('getVisibleAdminWidgetConfigs')
            ->once()
            ->with($homeTabA)
            ->andReturn($adminWidgetConfigsA);
        $this->homeTabManager
            ->shouldReceive('getVisibleWidgetConfigsByUser')
            ->once()
            ->with($homeTabA, $user)
            ->andReturn($userWidgetConfigsA);
        $homeTabConfigA->shouldReceive('getId')->once()->andReturn(1);
        $homeTabConfigB
            ->shouldReceive('getHomeTab')
            ->times(2)
            ->andReturn($homeTabB);
        $this->homeTabManager
            ->shouldReceive('getVisibleAdminWidgetConfigs')
            ->once()
            ->with($homeTabB)
            ->andReturn($adminWidgetConfigsB);
        $this->homeTabManager
            ->shouldReceive('getVisibleWidgetConfigsByUser')
            ->once()
            ->with($homeTabB, $user)
            ->andReturn($userWidgetConfigsB);
        $homeTabConfigB->shouldReceive('getId')->once()->andReturn(2);
        $homeTabConfigC
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabC);
        $this->homeTabManager
            ->shouldReceive('getVisibleWidgetConfigsByUser')
            ->once()
            ->with($homeTabC, $user)
            ->andReturn($userWidgetConfigsC);
        $homeTabConfigC->shouldReceive('getId')->once()->andReturn(3);
        $homeTabConfigD
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabD);
        $this->homeTabManager
            ->shouldReceive('getVisibleWidgetConfigsByUser')
            ->once()
            ->with($homeTabD, $user)
            ->andReturn($userWidgetConfigsD);
        $homeTabConfigD->shouldReceive('getId')->once()->andReturn(4);
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');

        $this->assertEquals(
            array(
                'adminHomeTabConfigs' => $adminHomeTabConfigs,
                'homeTabConfigs' => $homeTabConfigs,
                'tool' => 'tool',
                'nbWidgets' => $nbWidgets
            ),
            $controller->desktopHomeTabPropertiesAction()
        );
    }

    public function testUserDesktopHomeTabCreateFormAction()
    {
        $controller = $this->getController(array('checkUserAccess', 'getHomeTool'));
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('ROLE_USER')
            ->once()
            ->andReturn(true);
        $this->formFactory
            ->shouldReceive('create')
            ->with(
                FormFactory::TYPE_HOME_TAB,
                array(),
                anInstanceOf('Claroline\CoreBundle\Entity\Home\HomeTab')
            )
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')->once()->andReturn('view');
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');

        $this->assertEquals(
            array(
                'form' => 'view',
                'tool' => 'tool'
            ),
            $controller->userDesktopHomeTabCreateFormAction()
        );
    }

    public function testUserDesktopHomeTabCreateAction()
    {
        $controller = $this->getController(
            array('checkUserAccess', 'generateUrl', 'redirect')
        );
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('ROLE_USER')
            ->once()
            ->andReturn(true);
        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $this->formFactory
            ->shouldReceive('create')
            ->with(
                FormFactory::TYPE_HOME_TAB,
                array(),
                anInstanceOf('Claroline\CoreBundle\Entity\Home\HomeTab')
            )
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')->with($this->request)->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $this->homeTabManager
            ->shouldReceive('insertHomeTab')
            ->with(
                m::on(
                    function (HomeTab $homeTab) {

                        return $homeTab->getType() === 'desktop';
                    }
                )
            )
            ->once();
        $this->homeTabManager
            ->shouldReceive('getOrderOfLastDesktopHomeTabConfigByUser')
            ->with($user)
            ->once()
            ->andReturn(array('order_max' => 3));
        $this->homeTabManager
            ->shouldReceive('insertHomeTabConfig')
            ->with(
                m::on(
                    function (HomeTabConfig $homeTabConfig) {

                        return $homeTabConfig->getType() === 'desktop'
                            && !$homeTabConfig->isVisible()
                            && !$homeTabConfig->isLocked()
                            && $homeTabConfig->getTabOrder() === 4;
                    }
                )
            )
            ->once();
        $controller
            ->shouldReceive('generateUrl')
            ->with('claro_desktop_home_tab_properties')
            ->once()
            ->andReturn('url');
        $controller
            ->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->userDesktopHomeTabCreateAction()
        );
    }

    public function testUserDesktopHomeTabEditFormAction()
    {
        $controller = $this->getController(
            array('checkUserAccess', 'checkUserAccessForHomeTab')
        );
        $homeTabConfig = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $homeTabUser = $this->mock('Claroline\CoreBundle\Entity\User');
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('ROLE_USER')
            ->once()
            ->andReturn(true);
        $homeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $homeTab->shouldReceive('getUser')->once()->andReturn($homeTabUser);
        $homeTabUser->shouldReceive('getId')->once()->andReturn(1);
        $user->shouldReceive('getId')->once()->andReturn(1);
        $this->formFactory
            ->shouldReceive('create')
            ->with(FormFactory::TYPE_HOME_TAB, array(), $homeTab)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('createView')->once()->andReturn('view');
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');
        $homeTab->shouldReceive('getName')->once()->andReturn('name');

        $this->assertEquals(
            array(
                'form' => 'view',
                'tool' => 'tool',
                'homeTabConfig' => $homeTabConfig,
                'homeTab' => $homeTab,
                'homeTabName' => 'name'
            ),
            $controller->userDesktopHomeTabEditFormAction($homeTabConfig)
        );
    }

    public function testUserDesktopHomeTabEditAction()
    {
        $controller = $this->getController(
            array(
                'checkUserAccess',
                'checkUserAccessForHomeTab',
                'generateUrl',
                'redirect'
            )
        );
        $homeTabConfig = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $homeTabUser = $this->mock('Claroline\CoreBundle\Entity\User');
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('ROLE_USER')
            ->once()
            ->andReturn(true);
        $homeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $homeTab->shouldReceive('getUser')->once()->andReturn($homeTabUser);
        $user->shouldReceive('getId')->once()->andReturn(1);
        $homeTabUser->shouldReceive('getId')->once()->andReturn(1);
        $this->formFactory
            ->shouldReceive('create')
            ->with(FormFactory::TYPE_HOME_TAB, array(), $homeTab)
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')->with($this->request)->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $this->homeTabManager
            ->shouldReceive('insertHomeTab')
            ->with($homeTab)
            ->once();
        $controller
            ->shouldReceive('generateUrl')
            ->with('claro_desktop_home_tab_properties')
            ->once()
            ->andReturn('url');
        $controller
            ->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->userDesktopHomeTabEditAction($homeTabConfig, 'name')
        );
    }

    public function testUserDesktopHomeTabDeleteAction()
    {
        $controller = $this->getController(
            array('checkUserAccess', 'checkUserAccessForHomeTab')
        );
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = $this->mock('Claroline\CoreBundle\Entity\User');
        $homeTabUser = $this->mock('Claroline\CoreBundle\Entity\User');

        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('ROLE_USER')
            ->once()
            ->andReturn(true);
        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $homeTab->shouldReceive('getUser')->once()->andReturn($homeTabUser);
        $user->shouldReceive('getId')->once()->andReturn(1);
        $homeTabUser->shouldReceive('getId')->once()->andReturn(1);
        $this->homeTabManager
            ->shouldReceive('deleteHomeTab')
            ->with($homeTab, 'desktop', 1)
            ->once();

        $response = $controller->userDesktopHomeTabDeleteAction(
            $homeTab,
            1
        );
        $this->assertInstanceOf(
            'Symfony\Component\HttpFoundation\Response',
            $response
        );
        $this->assertEquals(
            'success',
            $response->getContent()
        );
        $this->assertEquals(
            204,
            $response->getStatusCode()
        );
    }

    public function testDisplayDesktopHomeTabsAction()
    {
        $token = $this->mock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface');
        $user = new User();
        $homeTabConfigA = new HomeTabConfig();
        $homeTabConfigB = new HomeTabConfig();
        $adminHomeTabConfigsTemp = array($homeTabConfigA, $homeTabConfigB);
        $adminHomeTabConfigs = array('admin_config_a', 'admin_config_b');
        $userHomeTabConfigs = array('user_config_a', 'user_config_b');

        $this->securityContext
            ->shouldReceive('getToken')
            ->once()
            ->andReturn($token);
        $token->shouldReceive('getUser')->once()->andReturn($user);
        $this->homeTabManager
            ->shouldReceive('generateAdminHomeTabConfigsByUser')
            ->with($user)
            ->once()
            ->andReturn($adminHomeTabConfigsTemp);
        $this->homeTabManager
            ->shouldReceive('filterVisibleHomeTabConfigs')
            ->with($adminHomeTabConfigsTemp)
            ->once()
            ->andReturn($adminHomeTabConfigs);
        $this->homeTabManager
            ->shouldReceive('getVisibleDesktopHomeTabConfigsByUser')
            ->with($user)
            ->once()
            ->andReturn($userHomeTabConfigs);

        $this->assertEquals(
            array(
                'adminHomeTabConfigs' => $adminHomeTabConfigs,
                'userHomeTabConfigs' => $userHomeTabConfigs,
                'tabId' => 1
            ),
            $this->getController()->displayDesktopHomeTabsAction(1)
        );
    }

    public function testWorkspaceHomeTabPropertiesAction()
    {
        $controller = $this->getController(
            array('checkWorkspaceAccess', 'getHomeTool')
        );
        $workspace = $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $adminConfigA = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $adminConfigB = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $configC = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $configD = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $adminHomeTabConfigs = array($adminConfigA, $adminConfigB);
        $homeTabConfigs = array($configC, $configD);
        $homeTabA = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $homeTabB = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $homeTabC = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $homeTabD = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $nbWidgets = array(
            1 => 3,
            2 => 2,
            3 => 2,
            4 => 0
        );
        $roleManager = $this->mock('Claroline\CoreBundle\Entity\Role');

        $this->roleManager
            ->shouldReceive('getManagerRole')
            ->with($workspace)
            ->once()
            ->andReturn($roleManager);
        $roleManager->shouldReceive('getName')->once()->andReturn('ROLE_MANAGER');
        $this->securityContext
            ->shouldReceive('isGranted')
            ->with('ROLE_MANAGER')
            ->once()
            ->andReturn(true);
        $this->homeTabManager
            ->shouldReceive('generateAdminHomeTabConfigsByWorkspace')
            ->with($workspace)
            ->once()
            ->andReturn($adminHomeTabConfigs);
        $this->homeTabManager
            ->shouldReceive('getWorkspaceHomeTabConfigsByWorkspace')
            ->with($workspace)
            ->once()
            ->andReturn($homeTabConfigs);
        $adminConfigA
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabA);
        $this->homeTabManager
            ->shouldReceive('getVisibleWidgetConfigsByWorkspace')
            ->with($homeTabA, $workspace)
            ->once()
            ->andReturn(array('w1', 'w2', 'w3'));
        $adminConfigA->shouldReceive('getId')->once()->andReturn(1);
        $adminConfigB
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabB);
        $this->homeTabManager
            ->shouldReceive('getVisibleWidgetConfigsByWorkspace')
            ->with($homeTabB, $workspace)
            ->once()
            ->andReturn(array('w4', 'w5'));
        $adminConfigB->shouldReceive('getId')->once()->andReturn(2);
        $configC->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabC);
        $this->homeTabManager
            ->shouldReceive('getVisibleWidgetConfigsByWorkspace')
            ->with($homeTabC, $workspace)
            ->once()
            ->andReturn(array('w6', 'w7'));
        $configC->shouldReceive('getId')->once()->andReturn(3);
        $configD->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabD);
        $this->homeTabManager
            ->shouldReceive('getVisibleWidgetConfigsByWorkspace')
            ->with($homeTabD, $workspace)
            ->once()
            ->andReturn(array());
        $configD->shouldReceive('getId')->once()->andReturn(4);
        $this->toolManager
            ->shouldReceive('getOneToolByName')
            ->with('home')
            ->once()
            ->andReturn('tool');

        $this->assertEquals(
            array(
                'workspace' => $workspace,
                'adminHomeTabConfigs' => $adminHomeTabConfigs,
                'homeTabConfigs' => $homeTabConfigs,
                'tool' => 'tool',
                'nbWidgets' => $nbWidgets
            ),
            $controller->workspaceHomeTabPropertiesAction($workspace)
        );
    }

    private function getController(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {

            return new HomeController(
                $this->em,
                $this->eventDispatcher,
                $this->formFactory,
                $this->homeTabManager,
                $this->request,
                $this->roleManager,
                $this->securityContext,
                $this->toolManager,
                $this->widgetManager
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Controller\Tool\HomeController' . $stringMocked,
            array(
                $this->em,
                $this->eventDispatcher,
                $this->formFactory,
                $this->homeTabManager,
                $this->request,
                $this->roleManager,
                $this->securityContext,
                $this->toolManager,
                $this->widgetManager
            )
        );
    }
}