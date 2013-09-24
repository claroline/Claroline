<?php

namespace Claroline\CoreBundle\Manager;

use \Mockery as m;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
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

        $this->om = $this->mock('Claroline\CoreBundle\Persistence\ObjectManager');
        $this->homeTabRepo =
            $this->mock('Claroline\CoreBundle\Repository\HomeTabRepository');
        $this->homeTabConfigRepo =
            $this->mock('Claroline\CoreBundle\Repository\HomeTabConfigRepository');
        $this->widgetHomeTabConfigRepo =
            $this->mock('Claroline\CoreBundle\Repository\WidgetHomeTabConfigRepository');
        $this->widgetDisplayConfigRepo =
            $this->mock('Claroline\CoreBundle\Repository\DisplayConfigRepository');
    }

    public function testInsertHomeTab()
    {
        $homeTab = new HomeTab();

        $this->om->shouldReceive('persist')->with($homeTab)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->insertHomeTab($homeTab);
    }

    public function testDeleteAdminDesktopHomeTab()
    {
        $homeTab = new HomeTab();

        $this->homeTabConfigRepo
            ->shouldReceive('updateAdminDesktopOrder')
            ->with(1)
            ->once();
        $this->om->shouldReceive('remove')->with($homeTab)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteHomeTab($homeTab, 'admin_desktop', 1);
    }

    public function testDeleteAdminWorkspaceHomeTab()
    {
        $homeTab = new HomeTab();

        $this->homeTabConfigRepo
            ->shouldReceive('updateAdminWorkspaceOrder')
            ->with(1)
            ->once();
        $this->om->shouldReceive('remove')->with($homeTab)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteHomeTab($homeTab, 'admin_workspace', 1);
    }

    public function testDeleteDesktopHomeTab()
    {
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $user = new User();

        $homeTab->shouldReceive('getUser')->once()->andReturn($user);
        $this->homeTabConfigRepo
            ->shouldReceive('updateDesktopOrder')
            ->with($user, 1)
            ->once();
        $this->om->shouldReceive('remove')->with($homeTab)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteHomeTab($homeTab, 'desktop', 1);
    }

    public function testDeleteWorkspaceHomeTab()
    {
        $homeTab = $this->mock('Claroline\CoreBundle\Entity\Home\HomeTab');
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $homeTab->shouldReceive('getWorkspace')->once()->andReturn($workspace);
        $this->homeTabConfigRepo
            ->shouldReceive('updateWorkspaceOrder')
            ->with($workspace, 1)
            ->once();
        $this->om->shouldReceive('remove')->with($homeTab)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteHomeTab($homeTab, 'workspace', 1);
    }

    public function testInsertHomeTabConfig()
    {
        $homeTabConfig = new HomeTabConfig();

        $this->om->shouldReceive('persist')->with($homeTabConfig)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->insertHomeTabConfig($homeTabConfig);
    }

    public function testUpdateVisibility()
    {
        $homeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $homeTabConfig->shouldReceive('setVisible')->with(true)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->updateVisibility($homeTabConfig, true);
    }

    public function testUpdateLock()
    {
        $homeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $homeTabConfig->shouldReceive('setLocked')->with(true)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->updateLock($homeTabConfig, true);
    }

    public function testCreateWorkspaceVersion()
    {
        $homeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $homeTab = new HomeTab();

        $homeTabConfig->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $homeTabConfig->shouldReceive('getType')
            ->once()
            ->andReturn('admin_workspace');
        $homeTabConfig->shouldReceive('isVisible')
            ->once()
            ->andReturn(true);
        $homeTabConfig->shouldReceive('isLocked')
            ->once()
            ->andReturn(false);
        $homeTabConfig->shouldReceive('getTabOrder')
            ->once()
            ->andReturn(1);
        $this->om->shouldReceive('persist')->once()->with(
            m::on(
                function (HomeTabConfig $newHomeTabConfig) {
                    return $newHomeTabConfig->getType() == 'admin_workspace'
                        && $newHomeTabConfig->isVisible()
                        && !$newHomeTabConfig->isLocked()
                        && $newHomeTabConfig->getTabOrder() === 1;
                }
            )
        );
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->createWorkspaceVersion($homeTabConfig, $workspace);
    }

    public function testCreateUserVersion()
    {
        $homeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $user = new User();
        $homeTab = new HomeTab();

        $homeTabConfig->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $homeTabConfig->shouldReceive('getType')
            ->once()
            ->andReturn('admin_desktop');
        $homeTabConfig->shouldReceive('isVisible')
            ->once()
            ->andReturn(true);
        $homeTabConfig->shouldReceive('isLocked')
            ->once()
            ->andReturn(false);
        $homeTabConfig->shouldReceive('getTabOrder')
            ->once()
            ->andReturn(1);
        $this->om->shouldReceive('persist')->once()->with(
            m::on(
                function (HomeTabConfig $newHomeTabConfig) {
                    return $newHomeTabConfig->getType() == 'admin_desktop'
                        && $newHomeTabConfig->isVisible()
                        && !$newHomeTabConfig->isLocked()
                        && $newHomeTabConfig->getTabOrder() === 1;
                }
            )
        );
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->createUserVersion($homeTabConfig, $user);
    }

    public function testGenerateAdminHomeTabConfigsByUser()
    {
        $user = new User();
        $adminHomeTabConfigA =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $adminHomeTabConfigB =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $adminHomeTabConfigs = array($adminHomeTabConfigA, $adminHomeTabConfigB);
        $homeTab = new HomeTab();
        $newHomeTabConfig = new HomeTabConfig();
        $manager = $this->getManager(array('createUserVersion'));

        $this->homeTabConfigRepo
            ->shouldReceive('findAdminDesktopHomeTabConfigs')
            ->once()
            ->andReturn($adminHomeTabConfigs);
        $adminHomeTabConfigA
            ->shouldReceive('isLocked')
            ->once()
            ->andReturn(true);
        $adminHomeTabConfigB
            ->shouldReceive('isLocked')
            ->once()
            ->andReturn(false);
        $adminHomeTabConfigB
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'user' => $user
                )
            )
            ->once()
            ->andReturn(null);
        $manager->shouldReceive('createUserVersion')
            ->with($adminHomeTabConfigB, $user)
            ->once()
            ->andReturn($newHomeTabConfig);

        $this->assertEquals(
            array($adminHomeTabConfigA, $newHomeTabConfig),
            $manager->generateAdminHomeTabConfigsByUser($user)
        );
    }


    public function testGenerateAdminHomeTabConfigsByWorkspace()
    {
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $adminHomeTabConfigA =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $adminHomeTabConfigB =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $adminHomeTabConfigs = array($adminHomeTabConfigA, $adminHomeTabConfigB);
        $homeTabA = new HomeTab();
        $homeTabB = new HomeTab();
        $existingHomeTabConfig = new HomeTabConfig();
        $newHomeTabConfig = new HomeTabConfig();
        $manager = $this->getManager(
            array(
                'createWorkspaceVersion',
                'generateAdminWidgetHomeTabConfigsByWorkspace'
            )
        );

        $this->homeTabConfigRepo
            ->shouldReceive('findAdminWorkspaceHomeTabConfigs')
            ->once()
            ->andReturn($adminHomeTabConfigs);
        $adminHomeTabConfigA
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabA);
        $adminHomeTabConfigB
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTabB);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTabA,
                    'workspace' => $workspace
                )
            )
            ->once()
            ->andReturn($existingHomeTabConfig);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTabB,
                    'workspace' => $workspace
                )
            )
            ->once()
            ->andReturn(null);
        $manager->shouldReceive('createWorkspaceVersion')
            ->with($adminHomeTabConfigB, $workspace)
            ->once()
            ->andReturn($newHomeTabConfig);
        $manager->shouldReceive('generateAdminWidgetHomeTabConfigsByWorkspace')
            ->with($homeTabB, $workspace)
            ->once();

        $this->assertEquals(
            array($existingHomeTabConfig, $newHomeTabConfig),
            $manager->generateAdminHomeTabConfigsByWorkspace($workspace)
        );
    }

    public function testGenerateAdminWidgetHomeTabConfigsByWorkspace()
    {
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $homeTab = new HomeTab();
        $adminWidgetHTCA =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $adminWidgetHTCB =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $adminWidgetHTCs = array($adminWidgetHTCA, $adminWidgetHTCB);
        $widgetA = new Widget();
        $widgetB = new Widget();

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findAdminWidgetConfigs')
            ->with($homeTab)
            ->once()
            ->andReturn($adminWidgetHTCs);
        $adminWidgetHTCA
            ->shouldReceive('getWidget')
            ->once()
            ->andReturn($widgetA);
        $adminWidgetHTCA
            ->shouldReceive('isVisible')
            ->once()
            ->andReturn(true);
        $adminWidgetHTCA
            ->shouldReceive('isLocked')
            ->once()
            ->andReturn(false);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findOrderOfLastWidgetInHomeTabByWorkspace')
            ->with($homeTab, $workspace)
            ->once()
            ->andReturn(null);
        $this->om->shouldReceive('persist')->once()->with(
            m::on(
                function (WidgetHomeTabConfig $widgetHomeTabConfig) {
                    return $widgetHomeTabConfig->getType() == 'workspace'
                        && $widgetHomeTabConfig->isVisible()
                        && !$widgetHomeTabConfig->isLocked()
                        && $widgetHomeTabConfig->getWidgetOrder() === 1;
                }
            )
        );
        $this->om->shouldReceive('flush')->times(2);

        $adminWidgetHTCB
            ->shouldReceive('getWidget')
            ->once()
            ->andReturn($widgetB);
        $adminWidgetHTCB
            ->shouldReceive('isVisible')
            ->once()
            ->andReturn(true);
        $adminWidgetHTCB
            ->shouldReceive('isLocked')
            ->once()
            ->andReturn(false);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findOrderOfLastWidgetInHomeTabByWorkspace')
            ->with($homeTab, $workspace)
            ->once()
            ->andReturn(1);
        $this->om->shouldReceive('persist')->once()->with(
            m::on(
                function (WidgetHomeTabConfig $widgetHomeTabConfig) {
                    return $widgetHomeTabConfig->getType() == 'workspace'
                        && $widgetHomeTabConfig->isVisible()
                        && !$widgetHomeTabConfig->isLocked()
                        && $widgetHomeTabConfig->getWidgetOrder() === 2;
                }
            )
        );

        $this->getManager()
            ->generateAdminWidgetHomeTabConfigsByWorkspace($homeTab, $workspace);
    }

    public function testFilterVisibleHomeTabConfigs()
    {
        $homeTabConfigA =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTabConfigB =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $homeTabConfigs = array($homeTabConfigA, $homeTabConfigB);

        $homeTabConfigA
            ->shouldReceive('isVisible')
            ->once()
            ->andReturn(true);
        $homeTabConfigB
            ->shouldReceive('isVisible')
            ->once()
            ->andReturn(false);

        $this->assertEquals(
            array($homeTabConfigA),
            $this->getManager()->filterVisibleHomeTabConfigs($homeTabConfigs)
        );
    }

    public function testCheckHomeTabVisibilityByUserCaseA()
    {
        $homeTab = new HomeTab();
        $user = new User();

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'type' => 'admin_desktop',
                    'user' => null,
                    'workspace' => null
                )
            )
            ->once()
            ->andReturn(null);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'user' => $user
                )
            )
            ->once()
            ->andReturn(null);

        $this->assertEquals(
            false,
            $this->getManager()->checkHomeTabVisibilityByUser($homeTab, $user)
        );
    }

    public function testCheckHomeTabVisibilityByUserCaseB()
    {
        $homeTab = new HomeTab();
        $user = new User();
        $adminHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'type' => 'admin_desktop',
                    'user' => null,
                    'workspace' => null
                )
            )
            ->once()
            ->andReturn($adminHomeTabConfig);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'user' => $user
                )
            )
            ->once()
            ->andReturn(null);
        $adminHomeTabConfig->shouldReceive('isVisible')->once()->andReturn(true);

        $this->assertEquals(
            true,
            $this->getManager()->checkHomeTabVisibilityByUser($homeTab, $user)
        );
    }

    public function testCheckHomeTabVisibilityByUserCaseC()
    {
        $homeTab = new HomeTab();
        $user = new User();
        $userHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'type' => 'admin_desktop',
                    'user' => null,
                    'workspace' => null
                )
            )
            ->once()
            ->andReturn(null);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'user' => $user
                )
            )
            ->once()
            ->andReturn($userHomeTabConfig);
        $userHomeTabConfig->shouldReceive('isVisible')->once()->andReturn(true);

        $this->assertEquals(
            true,
            $this->getManager()->checkHomeTabVisibilityByUser($homeTab, $user)
        );
    }

    public function testCheckHomeTabVisibilityByUserCaseD()
    {
        $homeTab = new HomeTab();
        $user = new User();
        $adminHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $userHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'type' => 'admin_desktop',
                    'user' => null,
                    'workspace' => null
                )
            )
            ->once()
            ->andReturn($adminHomeTabConfig);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'user' => $user
                )
            )
            ->once()
            ->andReturn($userHomeTabConfig);
        $adminHomeTabConfig->shouldReceive('isLocked')->once()->andReturn(false);
        $userHomeTabConfig->shouldReceive('isVisible')->once()->andReturn(false);

        $this->assertEquals(
            false,
            $this->getManager()->checkHomeTabVisibilityByUser($homeTab, $user)
        );
    }

    public function testCheckHomeTabVisibilityByWorkspaceCaseA()
    {
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'type' => 'admin_workspace',
                    'user' => null,
                    'workspace' => null
                )
            )
            ->once()
            ->andReturn(null);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'workspace' => $workspace
                )
            )
            ->once()
            ->andReturn(null);

        $this->assertEquals(
            false,
            $this->getManager()
                ->checkHomeTabVisibilityByWorkspace($homeTab, $workspace)
        );
    }

    public function testCheckHomeTabVisibilityByWorkspaceCaseB()
    {
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $adminHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'type' => 'admin_workspace',
                    'user' => null,
                    'workspace' => null
                )
            )
            ->once()
            ->andReturn($adminHomeTabConfig);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'workspace' => $workspace
                )
            )
            ->once()
            ->andReturn(null);
        $adminHomeTabConfig->shouldReceive('isVisible')->once()->andReturn(true);

        $this->assertEquals(
            true,
            $this->getManager()
                ->checkHomeTabVisibilityByWorkspace($homeTab, $workspace)
        );
    }

    public function testCheckHomeTabVisibilityByWorkspaceCaseC()
    {
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $workspaceHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'type' => 'admin_workspace',
                    'user' => null,
                    'workspace' => null
                )
            )
            ->once()
            ->andReturn(null);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'workspace' => $workspace
                )
            )
            ->once()
            ->andReturn($workspaceHomeTabConfig);
        $workspaceHomeTabConfig->shouldReceive('isVisible')->once()->andReturn(true);

        $this->assertEquals(
            true,
            $this->getManager()
                ->checkHomeTabVisibilityByWorkspace($homeTab, $workspace)
        );
    }

    public function testCheckHomeTabVisibilityByWorkspaceCaseD()
    {
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $adminHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');
        $workspaceHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Home\HomeTabConfig');

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'type' => 'admin_workspace',
                    'user' => null,
                    'workspace' => null
                )
            )
            ->once()
            ->andReturn($adminHomeTabConfig);
        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(
                array(
                    'homeTab' => $homeTab,
                    'workspace' => $workspace
                )
            )
            ->once()
            ->andReturn($workspaceHomeTabConfig);
        $adminHomeTabConfig->shouldReceive('isLocked')->once()->andReturn(false);
        $workspaceHomeTabConfig->shouldReceive('isVisible')->once()->andReturn(false);

        $this->assertEquals(
            false,
            $this->getManager()
                ->checkHomeTabVisibilityByWorkspace($homeTab, $workspace)
        );
    }

    public function testInsertWidgetHomeTabConfig()
    {
        $widgetHomeTabConfig = new WidgetHomeTabConfig();

        $this->om->shouldReceive('persist')->with($widgetHomeTabConfig)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->insertWidgetHomeTabConfig($widgetHomeTabConfig);
    }

    public function testDeleteWidgetHomeTabConfigCaseA()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $homeTab = new HomeTab();

        $widgetHomeTabConfig
            ->shouldReceive('getWidgetOrder')
            ->once()
            ->andReturn(1);
        $widgetHomeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $widgetHomeTabConfig
            ->shouldReceive('getUser')
            ->once()
            ->andReturn(null);
        $widgetHomeTabConfig
            ->shouldReceive('getWorkspace')
            ->once()
            ->andReturn(null);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('updateAdminWidgetHomeTabConfig')
            ->with($homeTab, 1)
            ->once();
        $this->om->shouldReceive('remove')->with($widgetHomeTabConfig)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteWidgetHomeTabConfig($widgetHomeTabConfig);
    }

    public function testDeleteWidgetHomeTabConfigCaseB()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $homeTab = new HomeTab();
        $user = new User();

        $widgetHomeTabConfig
            ->shouldReceive('getWidgetOrder')
            ->once()
            ->andReturn(1);
        $widgetHomeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $widgetHomeTabConfig
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($user);
        $widgetHomeTabConfig
            ->shouldReceive('getWorkspace')
            ->once()
            ->andReturn(null);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('updateWidgetHomeTabConfigByUser')
            ->with($homeTab, 1, $user)
            ->once();
        $this->om->shouldReceive('remove')->with($widgetHomeTabConfig)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteWidgetHomeTabConfig($widgetHomeTabConfig);
    }

    public function testDeleteWidgetHomeTabConfigCaseC()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $widgetHomeTabConfig
            ->shouldReceive('getWidgetOrder')
            ->once()
            ->andReturn(1);
        $widgetHomeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $widgetHomeTabConfig
            ->shouldReceive('getUser')
            ->once()
            ->andReturn(null);
        $widgetHomeTabConfig
            ->shouldReceive('getWorkspace')
            ->once()
            ->andReturn($workspace);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('updateWidgetHomeTabConfigByWorkspace')
            ->with($homeTab, 1, $workspace)
            ->once();
        $this->om->shouldReceive('remove')->with($widgetHomeTabConfig)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()->deleteWidgetHomeTabConfig($widgetHomeTabConfig);
    }

    public function testChangeOrderWidgetHomeTabConfigCaseA()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $homeTab = new HomeTab();
        $widgetHomeTabConfig
            ->shouldReceive('getWidgetOrder')
            ->once()
            ->andReturn(2);
        $widgetHomeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $widgetHomeTabConfig
            ->shouldReceive('getUser')
            ->once()
            ->andReturn(null);
        $widgetHomeTabConfig
            ->shouldReceive('getWorkspace')
            ->once()
            ->andReturn(null);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findOrderOfLastWidgetInAdminHomeTab')
            ->with($homeTab)
            ->once()
            ->andReturn(4);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('updateAdminWidgetOrder')
            ->with($homeTab, 3, 2)
            ->once()
            ->andReturn(4);
        $widgetHomeTabConfig->shouldReceive('setWidgetOrder')->with(3)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()
            ->changeOrderWidgetHomeTabConfig($widgetHomeTabConfig, 1);
    }

    public function testChangeOrderWidgetHomeTabConfigCaseB()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $homeTab = new HomeTab();
        $user = new User();

        $widgetHomeTabConfig
            ->shouldReceive('getWidgetOrder')
            ->once()
            ->andReturn(2);
        $widgetHomeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $widgetHomeTabConfig
            ->shouldReceive('getUser')
            ->once()
            ->andReturn($user);
        $widgetHomeTabConfig
            ->shouldReceive('getWorkspace')
            ->once()
            ->andReturn(null);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findOrderOfLastWidgetInHomeTabByUser')
            ->with($homeTab, $user)
            ->once()
            ->andReturn(4);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('updateWidgetOrderByUser')
            ->with($homeTab, 3, 2, $user)
            ->once()
            ->andReturn(4);
        $widgetHomeTabConfig->shouldReceive('setWidgetOrder')->with(3)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()
            ->changeOrderWidgetHomeTabConfig($widgetHomeTabConfig, 1);
    }

    public function testChangeOrderWidgetHomeTabConfigCaseC()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $widgetHomeTabConfig
            ->shouldReceive('getWidgetOrder')
            ->once()
            ->andReturn(2);
        $widgetHomeTabConfig
            ->shouldReceive('getHomeTab')
            ->once()
            ->andReturn($homeTab);
        $widgetHomeTabConfig
            ->shouldReceive('getUser')
            ->once()
            ->andReturn(null);
        $widgetHomeTabConfig
            ->shouldReceive('getWorkspace')
            ->once()
            ->andReturn($workspace);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findOrderOfLastWidgetInHomeTabByWorkspace')
            ->with($homeTab, $workspace)
            ->once()
            ->andReturn(4);
        $this->widgetHomeTabConfigRepo
            ->shouldReceive('updateWidgetOrderByWorkspace')
            ->with($homeTab, 1, 2, $workspace)
            ->once()
            ->andReturn(4);
        $widgetHomeTabConfig->shouldReceive('setWidgetOrder')->with(1)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()
            ->changeOrderWidgetHomeTabConfig($widgetHomeTabConfig, -1);
    }

    public function testChangeVisibilityWidgetHomeTabConfig()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');

        $widgetHomeTabConfig->shouldReceive('isVisible')->once()->andReturn(false);
        $widgetHomeTabConfig->shouldReceive('setVisible')->with(true)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()
            ->changeVisibilityWidgetHomeTabConfig($widgetHomeTabConfig);
    }

    public function testChangeLockWidgetHomeTabConfig()
    {
        $widgetHomeTabConfig =
            $this->mock('Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig');

        $widgetHomeTabConfig->shouldReceive('isLocked')->once()->andReturn(false);
        $widgetHomeTabConfig->shouldReceive('setLocked')->with(true)->once();
        $this->om->shouldReceive('flush')->once();

        $this->getManager()
            ->changeLockWidgetHomeTabConfig($widgetHomeTabConfig);
    }

    public function testGetHomeTabById()
    {
        m::getConfiguration()->allowMockingNonExistentMethods(true);
        $this->homeTabRepo
            ->shouldReceive('findOneById')
            ->with(1)
            ->once()
            ->andReturn('home_tab');
        m::getConfiguration()->allowMockingNonExistentMethods(false);

        $this->assertEquals(
            'home_tab',
            $this->getManager()->getHomeTabById(1)
        );
    }

    public function testGetAdminDesktopHomeTabConfigs()
    {
        $homeTabs = array('home_tab_A', 'home_tab_B');

        $this->homeTabConfigRepo
            ->shouldReceive('findAdminDesktopHomeTabConfigs')
            ->once()
            ->andReturn($homeTabs);

        $this->assertEquals(
            $homeTabs,
            $this->getManager()->getAdminDesktopHomeTabConfigs()
        );
    }

    public function testGetAdminWorkspaceHomeTabConfigs()
    {
        $homeTabConfigs = array('home_tab_config_A', 'home_tab_config_B');

        $this->homeTabConfigRepo
            ->shouldReceive('findAdminWorkspaceHomeTabConfigs')
            ->once()
            ->andReturn($homeTabConfigs);

        $this->assertEquals(
            $homeTabConfigs,
            $this->getManager()->getAdminWorkspaceHomeTabConfigs()
        );
    }

    public function testGetAdminDesktopHomeTabConfigByHomeTab()
    {
        $homeTab = new HomeTab();

        $this->homeTabConfigRepo
            ->shouldReceive('findAdminDesktopHomeTabConfigByHomeTab')
            ->with($homeTab)
            ->once()
            ->andReturn('home_tab_config');

        $this->assertEquals(
            'home_tab_config',
            $this->getManager()->getAdminDesktopHomeTabConfigByHomeTab($homeTab)
        );
    }

    public function testGetDesktopHomeTabConfigsByUser()
    {
        $user = new User();
        $homeTabConfigs = array('home_tab_config_A', 'home_tab_config_B');

        $this->homeTabConfigRepo
            ->shouldReceive('findDesktopHomeTabConfigsByUser')
            ->with($user)
            ->once()
            ->andReturn($homeTabConfigs);

        $this->assertEquals(
            $homeTabConfigs,
            $this->getManager()->getDesktopHomeTabConfigsByUser($user)
        );
    }

    public function testGetWorkspaceHomeTabConfigsByWorkspace()
    {
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $homeTabConfigs = array('home_tab_config_A', 'home_tab_config_B');

        $this->homeTabConfigRepo
            ->shouldReceive('findWorkspaceHomeTabConfigsByWorkspace')
            ->with($workspace)
            ->once()
            ->andReturn($homeTabConfigs);

        $this->assertEquals(
            $homeTabConfigs,
            $this->getManager()->getWorkspaceHomeTabConfigsByWorkspace($workspace)
        );
    }

    public function testGetVisibleAdminDesktopHomeTabConfigs()
    {
        $homeTabConfigs = array('home_tab_config_A', 'home_tab_config_B');

        $this->homeTabConfigRepo
            ->shouldReceive('findVisibleAdminDesktopHomeTabConfigs')
            ->once()
            ->andReturn($homeTabConfigs);

        $this->assertEquals(
            $homeTabConfigs,
            $this->getManager()->getVisibleAdminDesktopHomeTabConfigs()
        );
    }

    public function testGetVisibleAdminWorkspaceHomeTabConfigs()
    {
        $homeTabConfigs = array('home_tab_config_A', 'home_tab_config_B');

        $this->homeTabConfigRepo
            ->shouldReceive('findVisibleAdminWorkspaceHomeTabConfigs')
            ->once()
            ->andReturn($homeTabConfigs);

        $this->assertEquals(
            $homeTabConfigs,
            $this->getManager()->getVisibleAdminWorkspaceHomeTabConfigs()
        );
    }

    public function testGetVisibleDesktopHomeTabConfigsByUser()
    {
        $user = new User();
        $homeTabConfigs = array('home_tab_config_A', 'home_tab_config_B');

        $this->homeTabConfigRepo
            ->shouldReceive('findVisibleDesktopHomeTabConfigsByUser')
            ->with($user)
            ->once()
            ->andReturn($homeTabConfigs);

        $this->assertEquals(
            $homeTabConfigs,
            $this->getManager()->getVisibleDesktopHomeTabConfigsByUser($user)
        );
    }

    public function testGetVisibleWorkspaceHomeTabConfigsByWorkspace()
    {
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $homeTabConfigs = array('home_tab_config_A', 'home_tab_config_B');

        $this->homeTabConfigRepo
            ->shouldReceive('findVisibleWorkspaceHomeTabConfigsByWorkspace')
            ->with($workspace)
            ->once()
            ->andReturn($homeTabConfigs);

        $this->assertEquals(
            $homeTabConfigs,
            $this->getManager()->getVisibleWorkspaceHomeTabConfigsByWorkspace($workspace)
        );
    }

    public function testGetOrderOfLastDesktopHomeTabConfigByUser()
    {
        $user = new User();

        $this->homeTabConfigRepo
            ->shouldReceive('findOrderOfLastDesktopHomeTabByUser')
            ->with($user)
            ->once()
            ->andReturn(4);

        $this->assertEquals(
            4,
            $this->getManager()->getOrderOfLastDesktopHomeTabConfigByUser($user)
        );
    }

    public function testGetOrderOfLastWorkspaceHomeTabConfigByWorkspace()
    {
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

        $this->homeTabConfigRepo
            ->shouldReceive('findOrderOfLastWorkspaceHomeTabByWorkspace')
            ->with($workspace)
            ->once()
            ->andReturn(4);

        $this->assertEquals(
            4,
            $this->getManager()
                ->getOrderOfLastWorkspaceHomeTabConfigByWorkspace($workspace)
        );
    }

    public function testGetOrderOfLastAdminDesktopHomeTabConfig()
    {
        $this->homeTabConfigRepo
            ->shouldReceive('findOrderOfLastAdminDesktopHomeTab')
            ->once()
            ->andReturn(4);

        $this->assertEquals(
            4,
            $this->getManager()->getOrderOfLastAdminDesktopHomeTabConfig()
        );
    }

    public function testGetOrderOfLastAdminWorkspaceHomeTabConfig()
    {
        $this->homeTabConfigRepo
            ->shouldReceive('findOrderOfLastAdminWorkspaceHomeTab')
            ->once()
            ->andReturn(4);

        $this->assertEquals(
            4,
            $this->getManager()->getOrderOfLastAdminWorkspaceHomeTabConfig()
        );
    }

    public function testGetHomeTabConfigByHomeTabAndWorkspace()
    {
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $homeTabConfig = new HomeTabConfig();

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(array('homeTab' => $homeTab, 'workspace' => $workspace))
            ->once()
            ->andReturn($homeTabConfig);

        $this->assertEquals(
            $homeTabConfig,
            $this->getManager()
                ->getHomeTabConfigByHomeTabAndWorkspace($homeTab, $workspace)
        );
    }

    public function testGetHomeTabConfigByHomeTabAndUser()
    {
        $homeTab = new HomeTab();
        $user = new User();
        $homeTabConfig = new HomeTabConfig();

        $this->homeTabConfigRepo
            ->shouldReceive('findOneBy')
            ->with(array('homeTab' => $homeTab, 'user' => $user))
            ->once()
            ->andReturn($homeTabConfig);

        $this->assertEquals(
            $homeTabConfig,
            $this->getManager()
                ->getHomeTabConfigByHomeTabAndUser($homeTab, $user)
        );
    }

    public function testGetAdminWidgetConfigs()
    {
        $homeTab = new HomeTab();
        $adminWidgetConfigs = array('whtc_a', 'whtc_b');

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findAdminWidgetConfigs')
            ->with($homeTab)
            ->once()
            ->andReturn($adminWidgetConfigs);

        $this->assertEquals(
            $adminWidgetConfigs,
            $this->getManager()->getAdminWidgetConfigs($homeTab)
        );
    }

    public function testGetVisibleAdminWidgetConfigs()
    {
        $homeTab = new HomeTab();
        $adminWidgetConfigs = array('whtc_a', 'whtc_b');

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findVisibleAdminWidgetConfigs')
            ->with($homeTab)
            ->once()
            ->andReturn($adminWidgetConfigs);

        $this->assertEquals(
            $adminWidgetConfigs,
            $this->getManager()->getVisibleAdminWidgetConfigs($homeTab)
        );
    }

    public function testGetWidgetConfigsByUser()
    {
        $homeTab = new HomeTab();
        $user = new User();
        $adminWidgetConfigs = array('whtc_a', 'whtc_b');

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findWidgetConfigsByUser')
            ->with($homeTab, $user)
            ->once()
            ->andReturn($adminWidgetConfigs);

        $this->assertEquals(
            $adminWidgetConfigs,
            $this->getManager()->getWidgetConfigsByUser($homeTab, $user)
        );
    }

    public function testGetVisibleWidgetConfigsByUser()
    {
        $homeTab = new HomeTab();
        $user = new User();
        $adminWidgetConfigs = array('whtc_a', 'whtc_b');

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findVisibleWidgetConfigsByUser')
            ->with($homeTab, $user)
            ->once()
            ->andReturn($adminWidgetConfigs);

        $this->assertEquals(
            $adminWidgetConfigs,
            $this->getManager()->getVisibleWidgetConfigsByUser($homeTab, $user)
        );
    }

    public function testGetWidgetConfigsByWorkspace()
    {
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $adminWidgetConfigs = array('whtc_a', 'whtc_b');

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findWidgetConfigsByWorkspace')
            ->with($homeTab, $workspace)
            ->once()
            ->andReturn($adminWidgetConfigs);

        $this->assertEquals(
            $adminWidgetConfigs,
            $this->getManager()->getWidgetConfigsByWorkspace($homeTab, $workspace)
        );
    }

    public function testGetVisibleWidgetConfigsByWorkspace()
    {
        $homeTab = new HomeTab();
        $workspace =
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');
        $adminWidgetConfigs = array('whtc_a', 'whtc_b');

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findVisibleWidgetConfigsByWorkspace')
            ->with($homeTab, $workspace)
            ->once()
            ->andReturn($adminWidgetConfigs);

        $this->assertEquals(
            $adminWidgetConfigs,
            $this->getManager()
                ->getVisibleWidgetConfigsByWorkspace($homeTab, $workspace)
        );
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
            $this->mock('Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace');

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

    public function testGetUserAdminWidgetHomeTabConfig()
    {
        $homeTab = new HomeTab();
        $widget = new Widget();
        $user = new User();
        $widgetHomeTabConfig = new WidgetHomeTabConfig();

        $this->widgetHomeTabConfigRepo
            ->shouldReceive('findUserAdminWidgetHomeTabConfig')
            ->with($homeTab, $widget, $user)
            ->once()
            ->andReturn($widgetHomeTabConfig);

        $this->assertEquals(
            $widgetHomeTabConfig,
            $this->getManager()
                ->getUserAdminWidgetHomeTabConfig($homeTab, $widget, $user)
        );
    }

    public function testGetVisibleDesktopWidgetConfigWithoutExcludedWidgets()
    {
        $excludedWidgets = array();
        $widgetDisplayConfigs = array('widget_dc_a', 'widget_dc_b');
        $this->widgetDisplayConfigRepo
            ->shouldReceive('findBy')
            ->with(
                array(
                    'parent' => null,
                    'isDesktop' => true,
                    'isVisible' => true
                )
            )
            ->once()
            ->andReturn($widgetDisplayConfigs);

        $this->assertEquals(
            $widgetDisplayConfigs,
            $this->getManager()
                ->getVisibleDesktopWidgetConfig($excludedWidgets)
        );
    }

    public function testGetVisibleDesktopWidgetConfigWithExcludedWidgets()
    {
        $excludedWidgets = array('excluded_widget_a', 'excluded_widget_b');
        $widgetDisplayConfigs = array('widget_dc_a', 'widget_dc_b');
        $this->widgetDisplayConfigRepo
            ->shouldReceive('findVisibleAdminDesktopWidgetDisplayConfig')
            ->with($excludedWidgets)
            ->once()
            ->andReturn($widgetDisplayConfigs);

        $this->assertEquals(
            $widgetDisplayConfigs,
            $this->getManager()
                ->getVisibleDesktopWidgetConfig($excludedWidgets)
        );
    }

    public function testGetVisibleWorkspaceWidgetConfigWithoutExcludedWidgets()
    {
        $excludedWidgets = array();
        $widgetDisplayConfigs = array('widget_dc_a', 'widget_dc_b');
        $this->widgetDisplayConfigRepo
            ->shouldReceive('findBy')
            ->with(
                array(
                    'parent' => null,
                    'isDesktop' => false,
                    'isVisible' => true
                )
            )
            ->once()
            ->andReturn($widgetDisplayConfigs);

        $this->assertEquals(
            $widgetDisplayConfigs,
            $this->getManager()
                ->getVisibleWorkspaceWidgetConfig($excludedWidgets)
        );
    }

    public function testGetVisibleWorkspaceWidgetConfigWithExcludedWidgets()
    {
        $excludedWidgets = array('excluded_widget_a', 'excluded_widget_b');
        $widgetDisplayConfigs = array('widget_dc_a', 'widget_dc_b');
        $this->widgetDisplayConfigRepo
            ->shouldReceive('findVisibleAdminWorkspaceWidgetDisplayConfig')
            ->with($excludedWidgets)
            ->once()
            ->andReturn($widgetDisplayConfigs);

        $this->assertEquals(
            $widgetDisplayConfigs,
            $this->getManager()
                ->getVisibleWorkspaceWidgetConfig($excludedWidgets)
        );
    }

    private function getManager(array $mockedMethods = array())
    {
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Home\HomeTab')
            ->once()
            ->andReturn($this->homeTabRepo);
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Home\HomeTabConfig')
            ->once()
            ->andReturn($this->homeTabConfigRepo);
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Widget\WidgetHomeTabConfig')
            ->once()
            ->andReturn($this->widgetHomeTabConfigRepo);
        $this->om->shouldReceive('getRepository')
            ->with('ClarolineCoreBundle:Widget\WidgetInstance')
            ->once()
            ->andReturn($this->widgetDisplayConfigRepo);

        if (count($mockedMethods) === 0) {
            return new HomeTabManager($this->om);
        }

        $stringMocked = '[';
        $stringMocked .= array_pop($mockedMethods);

        foreach ($mockedMethods as $mockedMethod) {
            $stringMocked .= ",{$mockedMethod}";
        }

        $stringMocked .= ']';

        return $this->mock(
            'Claroline\CoreBundle\Manager\HomeTabManager' . $stringMocked,
            array($this->om)
        );
    }
}
