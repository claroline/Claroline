<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;

class HomeTabManagerTest extends MockeryTestCase
{
    private $om;
    private $homeTabRepo;
    private $homeTabConfigRepo;
    private $widgetHomeTabConfigRepo;
    private $widgetDisplayConfigRepo;

    public function setUp()
    {
        parent::setUp();

        $this->om = $this->mock('Claroline\AppBundle\Persistence\ObjectManager');
        $this->homeTabRepo =
            $this->mock('Claroline\CoreBundle\Repository\HomeTabRepository');
        $this->homeTabConfigRepo =
            $this->mock('Claroline\CoreBundle\Repository\HomeTabConfigRepository');
        $this->widgetHomeTabConfigRepo =
            $this->mock('Claroline\CoreBundle\Repository\WidgetInstanceConfigRepository');
        $this->widgetDisplayConfigRepo =
            $this->mock('Claroline\CoreBundle\Repository\DisplayConfigRepository');
    }

    public function testGetOrderOfLastWidgetInAdminHomeTab()
    {
        $homeTab = new HomeTab();

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findOrderOfLastWidgetInAdminHomeTab')
            ->with($homeTab)
            ->once()
            ->andReturn(4);

        $this->assertEquals(
            4,
            $this->getManager()->getOrderOfLastWidgetInAdminHomeTab($homeTab)
        );
    }

    public function testGetOrderOfLastWidgetInHomeTabByUser()
    {
        $homeTab = new HomeTab();
        $user = new User();

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findOrderOfLastWidgetInHomeTabByUser')
            ->with($homeTab, $user)
            ->once()
            ->andReturn(4);

        $this->assertEquals(
            4,
            $this->getManager()
                ->getOrderOfLastWidgetInHomeTabByUser($homeTab, $user)
        );
    }

    public function testGetOrderOfLastWidgetInHomeTabByWorkspace()
    {
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\Workspace');

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findOrderOfLastWidgetInHomeTabByWorkspace')
            ->with($homeTab, $workspace)
            ->once()
            ->andReturn(4);

        $this->assertEquals(
            4,
            $this->getManager()
                ->getOrderOfLastWidgetInHomeTabByWorkspace($homeTab, $workspace)
        );
    }

    private function getManager(array $mockedMethods = [])
    {
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Tab\HomeTab')
            ->once()
            ->andReturn($this->homeTabRepo);
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Tab\HomeTabConfig')
            ->once()
            ->andReturn($this->homeTabConfigRepo);
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Widget\WidgetInstanceConfig')
            ->once()
            ->andReturn($this->widgetHomeTabConfigRepo);
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Widget\WidgetInstance')
            ->once()
            ->andReturn($this->widgetDisplayConfigRepo);

        if (0 === count($mockedMethods)) {
            return new HomeTabManager($this->om);
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Manager\HomeTabManager'.$stringMocked,
            [$this->om]
        );
    }
}
