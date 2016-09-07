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

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Form\Factory\FormFactory;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Mockery as m;

class AdministrationHomeTabControllerTest extends MockeryTestCase
{
    private $formFactory;
    private $homeTabManager;
    private $request;
    private $widgetManager;

    protected function setUp()
    {
        $this->markTestSkipped();
        parent::setUp();
        $this->formFactory = $this->mock('Claroline\CoreBundle\Form\Factory\FormFactory');
        $this->homeTabManager = $this->mock('Claroline\CoreBundle\Manager\HomeTabManager');
        $this->request = $this->mock('Symfony\Component\HttpFoundation\Request');
        $this->widgetManager = $this->mock('Claroline\CoreBundle\Manager\WidgetManager');
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

    public function testAssociateWidgetToHomeTabAction()
    {
        $homeTab = new HomeTab();
        $widget = new Widget();

        $this->homeTabManager
            ->shouldReceive('getOrderOfLastWidgetInAdminHomeTab')
            ->with($homeTab)
            ->once()
            ->andReturn(['order_max' => 3]);
        $this->homeTabManager
            ->shouldReceive('insertWidgetHomeTabConfig')
            ->with(
                m::on(
                    function (WidgetHomeTabConfig $widgetHomeTabConfig) {
                        return $widgetHomeTabConfig->getType() === 'admin'
                            && $widgetHomeTabConfig->isVisible()
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

    private function getController(array $mockedMethods = [])
    {
        if (count($mockedMethods) === 0) {
            return new AdministrationHomeTabController(
                $this->formFactory,
                $this->homeTabManager,
                $this->request,
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
            'Claroline\CoreBundle\Controller\AdministrationHomeTabController'.$stringMocked,
            [
                $this->formFactory,
                $this->homeTabManager,
                $this->request,
                $this->widgetManager,
            ]
        );
    }
}
