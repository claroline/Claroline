<?php

namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class AdministrationHomeTabControllerTest extends MockeryTestCase
{
    private $formFactory;
    private $homeTabManager;
    private $request;

    protected function setUp()
    {
        parent::setUp();
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->homeTabManager = $this->mock('Claroline\CoreBundle\Manager\HomeTabManager');
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
    }

    public function testAdminHomeTabsConfigAction()
    {
        $homeTabConfigA = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTabConfigB = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTabConfigC = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTabConfigD = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $desktopHomeTabConfigs = array($homeTabConfigA, $homeTabConfigB);
        $workspaceHomeTabConfigs = array($homeTabConfigC, $homeTabConfigD);
        $homeTabA = new HomeTab();
        $homeTabB = new HomeTab();
        $homeTabC = new HomeTab();
        $homeTabD = new HomeTab();
        $widgetConfigsA = array('widget_a_1', 'widget_a_2', 'widget_a_3');
        $widgetConfigsB = array('widget_b_1');
        $widgetConfigsC = array('widget_c_1', 'widget_c_2');
        $widgetConfigsD = array();
        $result = array(
            'desktopHomeTabConfigs' => $desktopHomeTabConfigs,
            'workspaceHomeTabConfigs' => $workspaceHomeTabConfigs,
            'nbWidgets' => array(
                1 => 3,
                2 => 1,
                3 => 2,
                4 => 0,
            )
        );

        $this->homeTabManager
            ->shouldReceive('getAdminDesktopHomeTabConfigs')
            ->once()
            ->andReturn($desktopHomeTabConfigs);
        $this->homeTabManager
            ->shouldReceive('getAdminWorkspaceHomeTabConfigs')
            ->once()
            ->andReturn($workspaceHomeTabConfigs);
        $homeTabConfigA
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabA);
        $homeTabConfigB
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabB);
        $homeTabConfigC
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabC);
        $homeTabConfigD
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabD);
        $this->homeTabManager
            ->shouldReceive('getVisibleAdminWidgetConfigs')
            ->with($homeTabA)
            ->once()
            ->andReturn($widgetConfigsA);
        $this->homeTabManager
            ->shouldReceive('getVisibleAdminWidgetConfigs')
            ->with($homeTabB)
            ->once()
            ->andReturn($widgetConfigsB);
        $this->homeTabManager
            ->shouldReceive('getVisibleAdminWidgetConfigs')
            ->with($homeTabC)
            ->once()
            ->andReturn($widgetConfigsC);
        $this->homeTabManager
            ->shouldReceive('getVisibleAdminWidgetConfigs')
            ->with($homeTabD)
            ->once()
            ->andReturn($widgetConfigsD);
        $homeTabConfigA
            ->shouldReceive('getId')
            ->once()
            ->andReturn(1);
        $homeTabConfigB
            ->shouldReceive('getId')
            ->once()
            ->andReturn(2);
        $homeTabConfigC
            ->shouldReceive('getId')
            ->once()
            ->andReturn(3);
        $homeTabConfigD
            ->shouldReceive('getId')
            ->once()
            ->andReturn(4);

        $this->assertEquals(
            $result,
            $this->getController()->adminHomeTabsConfigAction()
        );
    }

    public function testAdminDesktopHomeTabCreateFormAction()
    {
        $form = $this->mock('Symfony\Component\Form\Form');

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

        $this->assertEquals(
            array('form' => 'view'),
            $this->getController()->adminDesktopHomeTabCreateFormAction()
        );
    }

    public function testAdminDesktopHomeTabCreateAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->formFactory
            ->shouldReceive('create')
            ->with(
                FormFactory::TYPE_HOME_TAB,
                array(),
                anInstanceOf('Claroline\CoreBundle\Entity\Home\HomeTab')
            )
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $this->homeTabManager
            ->shouldReceive('insertHomeTab')
            ->with(
                m::on(
                    function (HomeTab $newHomeTab) {

                        return $newHomeTab->getType() === 'admin_desktop';
                    }
                )
            )
            ->once();
        $this->homeTabManager
            ->shouldReceive('getOrderOfLastAdminDesktopHomeTabConfig')
            ->once()
            ->andReturn(array('order_max' => 3));
        $this->homeTabManager
            ->shouldReceive('insertHomeTabConfig')
            ->with(
                m::on(
                    function (HomeTabConfig $newHomeTabConfig) {

                        return $newHomeTabConfig->getType() === 'admin_desktop'
                            && !$newHomeTabConfig->isVisible()
                            && !$newHomeTabConfig->isLocked()
                            && $newHomeTabConfig->getTabOrder() === 4;
                    }
                )
            )
            ->once();
        $controller
            ->shouldReceive('generateUrl')
            ->with('claro_admin_home_tabs_configuration')
            ->once()
            ->andReturn('url');
        $controller
            ->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->adminDesktopHomeTabCreateAction()
        );
    }

    public function testAdminWorkspaceHomeTabCreateFormAction()
    {
        $form = $this->mock('Symfony\Component\Form\Form');

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

        $this->assertEquals(
            array('form' => 'view'),
            $this->getController()->adminWorkspaceHomeTabCreateFormAction()
        );
    }

    public function testAdminWorkspaceHomeTabCreateAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $form = $this->mock('Symfony\Component\Form\Form');

        $this->formFactory
            ->shouldReceive('create')
            ->with(
                FormFactory::TYPE_HOME_TAB,
                array(),
                anInstanceOf('Claroline\CoreBundle\Entity\Home\HomeTab')
            )
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $this->homeTabManager
            ->shouldReceive('insertHomeTab')
            ->with(
                m::on(
                    function (HomeTab $newHomeTab) {

                        return $newHomeTab->getType() === 'admin_workspace';
                    }
                )
            )
            ->once();
        $this->homeTabManager
            ->shouldReceive('getOrderOfLastAdminWorkspaceHomeTabConfig')
            ->once()
            ->andReturn(array('order_max' => 3));
        $this->homeTabManager
            ->shouldReceive('insertHomeTabConfig')
            ->with(
                m::on(
                    function (HomeTabConfig $newHomeTabConfig) {

                        return $newHomeTabConfig->getType() === 'admin_workspace'
                            && !$newHomeTabConfig->isVisible()
                            && !$newHomeTabConfig->isLocked()
                            && $newHomeTabConfig->getTabOrder() === 4;
                    }
                )
            )
            ->once();
        $controller
            ->shouldReceive('generateUrl')
            ->with('claro_admin_home_tabs_configuration')
            ->once()
            ->andReturn('url');
        $controller
            ->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->adminWorkspaceHomeTabCreateAction()
        );
    }

    public function testAdminDesktopHomeTabEditFormAction()
    {
        $homeTabConfig = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $form = $this->mock('Symfony\Component\Form\Form');

        $homeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $homeTab->shouldReceive('getName')
            ->once()
            ->andReturn('name');
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

        $this->assertEquals(
            array(
                'form' => 'view',
                'homeTabConfig' => $homeTabConfig,
                'homeTab' => $homeTab,
                'homeTabName' => 'name'
            ),
            $this->getController()->adminDesktopHomeTabEditFormAction($homeTabConfig)
        );
    }

    public function testAdminWorkspaceHomeTabEditFormAction()
    {
        $homeTabConfig = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $form = $this->mock('Symfony\Component\Form\Form');

        $homeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $homeTab->shouldReceive('getName')
            ->once()
            ->andReturn('name');
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

        $this->assertEquals(
            array(
                'form' => 'view',
                'homeTabConfig' => $homeTabConfig,
                'homeTab' => $homeTab,
                'homeTabName' => 'name'
            ),
            $this->getController()->adminWorkspaceHomeTabEditFormAction($homeTabConfig)
        );
    }

    public function testAdminDesktopHomeTabEditAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $homeTabConfig = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTab = new HomeTab();
        $form = $this->mock('Symfony\Component\Form\Form');

        $homeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $this->formFactory
            ->shouldReceive('create')
            ->with(
                FormFactory::TYPE_HOME_TAB,
                array(),
                anInstanceOf('Claroline\CoreBundle\Entity\Home\HomeTab')
            )
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $this->homeTabManager
            ->shouldReceive('insertHomeTab')
            ->with($homeTab)
            ->once();
        $controller
            ->shouldReceive('generateUrl')
            ->with('claro_admin_home_tabs_configuration')
            ->once()
            ->andReturn('url');
        $controller
            ->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->adminDesktopHomeTabEditAction($homeTabConfig, 'name')
        );
    }

    public function testAdminWorkspaceHomeTabEditAction()
    {
        $controller = $this->getController(array('redirect', 'generateUrl'));
        $homeTabConfig = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTab = new HomeTab();
        $form = $this->mock('Symfony\Component\Form\Form');

        $homeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $this->formFactory
            ->shouldReceive('create')
            ->with(
                FormFactory::TYPE_HOME_TAB,
                array(),
                anInstanceOf('Claroline\CoreBundle\Entity\Home\HomeTab')
            )
            ->once()
            ->andReturn($form);
        $form->shouldReceive('handleRequest')
            ->with($this->request)
            ->once();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturn(true);
        $this->homeTabManager
            ->shouldReceive('insertHomeTab')
            ->with($homeTab)
            ->once();
        $controller
            ->shouldReceive('generateUrl')
            ->with('claro_admin_home_tabs_configuration')
            ->once()
            ->andReturn('url');
        $controller
            ->shouldReceive('redirect')
            ->with('url')
            ->once()
            ->andReturn('redirection');

        $this->assertEquals(
            'redirection',
            $controller->adminWorkspaceHomeTabEditAction($homeTabConfig, 'name')
        );
    }

    public function testAdminHomeTabDeleteAction()
    {
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');

        $homeTab->shouldReceive('getUser')->once()->andReturn(null);
        $homeTab->shouldReceive('getWorkspace')->once()->andReturn(null);
        $homeTab->shouldReceive('getType')->once()->andReturn('type');
        $this->homeTabManager
            ->shouldReceive('deleteHomeTab')
            ->with($homeTab, 'type', 1)
            ->once();

        $response = $this->getController()->adminHomeTabDeleteAction($homeTab, 1);
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

    public function testAdminHomeTabUpdateVisibilityAction()
    {
        $homeTabConfig = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $this->homeTabManager
            ->shouldReceive('updateVisibility')
            ->with($homeTabConfig, true)
            ->once();

        $response = $this->getController()
            ->adminHomeTabUpdateVisibilityAction($homeTabConfig, 'visible');
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

    public function testAdminHomeTabUpdateLockAction()
    {
        $homeTabConfig = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $this->homeTabManager
            ->shouldReceive('updateLock')
            ->with($homeTabConfig, true)
            ->once();

        $response = $this->getController()
            ->adminHomeTabUpdateLockAction($homeTabConfig, 'locked');
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

    public function testAdminHomeTabWidgetsConfigAction()
    {
        $homeTab = new HomeTab();
        $widgetConfigs = array('widget_config_a', 'widget_config_b');

        $this->homeTabManager
            ->shouldReceive('getAdminWidgetConfigs')
            ->with($homeTab)
            ->once()
            ->andReturn($widgetConfigs);
        $this->homeTabManager
            ->shouldReceive('getOrderOfLastWidgetInAdminHomeTab')
            ->with($homeTab)
            ->once()
            ->andReturn(array('order_max' => 4));

        $this->assertEquals(
            array(
                'homeTab' => $homeTab,
                'widgetConfigs' => $widgetConfigs,
                'lastWidgetOrder' => 4
            ),
            $this->getController()->adminHomeTabWidgetsConfigAction($homeTab)
        );
    }

    public function testListDesktopAddableWidgetsAction()
    {
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $widgetDisplayConfigs = array('widget_display_a', 'widget_display_b');
        $widgetConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $widget = $this->mock('Claroline\CoreBundle\Entity\Widget\Widget');

        $this->homeTabManager
            ->shouldReceive('getAdminWidgetConfigs')
            ->with($homeTab)
            ->once()
            ->andReturn(array($widgetConfig));
        $widgetConfig->shouldReceive('getWidget')->once()->andReturn($widget);
        $widget->shouldReceive('getId')->once()->andReturn(1);
        $homeTab->shouldReceive('getType')->once()->andReturn('admin_desktop');
        $this->homeTabManager
            ->shouldReceive('getVisibleDesktopWidgetConfig')
            ->with(array(1))
            ->once()
            ->andReturn($widgetDisplayConfigs);

        $this->assertEquals(
            array(
                'homeTab' => $homeTab,
                'widgetDisplayConfigs' => $widgetDisplayConfigs
            ),
            $this->getController()->listAddableWidgetsAction($homeTab)
        );
    }

    public function testListWorkspaceAddableWidgetsAction()
    {
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $widgetDisplayConfigs = array('widget_display_a', 'widget_display_b');
        $widgetConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $widget = $this->mock('Claroline\CoreBundle\Entity\Widget\Widget');

        $this->homeTabManager
            ->shouldReceive('getAdminWidgetConfigs')
            ->with($homeTab)
            ->once()
            ->andReturn(array($widgetConfig));
        $widgetConfig->shouldReceive('getWidget')->once()->andReturn($widget);
        $widget->shouldReceive('getId')->once()->andReturn(1);
        $homeTab->shouldReceive('getType')->once()->andReturn('admin_workspace');
        $this->homeTabManager
            ->shouldReceive('getVisibleWorkspaceWidgetConfig')
            ->with(array(1))
            ->once()
            ->andReturn($widgetDisplayConfigs);

        $this->assertEquals(
            array(
                'homeTab' => $homeTab,
                'widgetDisplayConfigs' => $widgetDisplayConfigs
            ),
            $this->getController()->listAddableWidgetsAction($homeTab)
        );
    }

    public function testAssociateWidgetToHomeTabAction()
    {
        $homeTab = new HomeTab();
        $widget = new Widget();

        $this->homeTabManager
            ->shouldReceive('getOrderOfLastWidgetInAdminHomeTab')
            ->with($homeTab)
            ->once()
            ->andReturn(array('order_max' => 3));
        $this->homeTabManager
            ->shouldReceive('insertWidgetHomeTabConfig')
            ->with(
                m::on(
                    function (WidgetHomeTabConfig $widgetHomeTabConfig) {

                        return $widgetHomeTabConfig->getType() === 'admin'
                            && !$widgetHomeTabConfig->isVisible()
                            && !$widgetHomeTabConfig->isLocked()
                            && $widgetHomeTabConfig->getWidgetOrder() === 4;
                    }
                )
            )
            ->once();

        $response = $this->getController()
            ->associateWidgetToHomeTabAction($homeTab, $widget);
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

    public function testAdminWidgetHomeTabConfigDeleteAction()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');

        $widgetHomeTabConfig->shouldReceive('getUser')->once()->andReturn(null);
        $widgetHomeTabConfig->shouldReceive('getWorkspace')->once()->andReturn(null);
        $this->homeTabManager
            ->shouldReceive('deleteWidgetHomeTabConfig')
            ->with($widgetHomeTabConfig)
            ->once();

        $response = $this->getController()
            ->adminWidgetHomeTabConfigDeleteAction($widgetHomeTabConfig);
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

    public function testAdminWidgetHomeTabConfigChangeOrderAction()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');

        $widgetHomeTabConfig->shouldReceive('getUser')->once()->andReturn(null);
        $widgetHomeTabConfig->shouldReceive('getWorkspace')->once()->andReturn(null);
        $this->homeTabManager
            ->shouldReceive('changeOrderWidgetHomeTabConfig')
            ->with($widgetHomeTabConfig, 1)
            ->once();

        $response = $this->getController()
            ->adminWidgetHomeTabConfigChangeOrderAction($widgetHomeTabConfig, 1);
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

    public function testAdminWidgetHomeTabConfigChangeVisibilityAction()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');

        $widgetHomeTabConfig->shouldReceive('getUser')->once()->andReturn(null);
        $widgetHomeTabConfig->shouldReceive('getWorkspace')->once()->andReturn(null);
        $this->homeTabManager
            ->shouldReceive('changeVisibilityWidgetHomeTabConfig')
            ->with($widgetHomeTabConfig)
            ->once();

        $response = $this->getController()
            ->adminWidgetHomeTabConfigChangeVisibilityAction($widgetHomeTabConfig);
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

    public function testAdminWidgetHomeTabConfigChangeLockAction()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');

        $widgetHomeTabConfig->shouldReceive('getUser')->once()->andReturn(null);
        $widgetHomeTabConfig->shouldReceive('getWorkspace')->once()->andReturn(null);
        $this->homeTabManager
            ->shouldReceive('changeLockWidgetHomeTabConfig')
            ->with($widgetHomeTabConfig)
            ->once();

        $response = $this->getController()
            ->adminWidgetHomeTabConfigChangeLockAction($widgetHomeTabConfig);
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

    private function getController(array $mockedMethods = array())
    {
        if (count($mockedMethods) === 0) {

            return new AdministrationHomeTabController(
                $this->formFactory,
                $this->homeTabManager,
                $this->request
            );
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Controller\AdministrationHomeTabController' . $stringMocked,
            array(
                $this->formFactory,
                $this->homeTabManager,
                $this->request
            )
        );
    }
}