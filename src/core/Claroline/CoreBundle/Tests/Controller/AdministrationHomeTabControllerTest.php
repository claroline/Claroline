<?php

namespace Claroline\CoreBundle\Controller;

use \Mockery as m;
use Claroline\CoreBundle\Entity\Home\HomeTab;
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