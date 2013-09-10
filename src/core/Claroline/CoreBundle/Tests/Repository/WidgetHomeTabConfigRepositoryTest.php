<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class WidgetHomeTabConfigRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Widget\WidgetHomeTabConfig');
        self::createUser('user_a');
        self::createWorkspace('wsa');
        self::createAdminHomeTab('adht', 'admin_desktop');
        self::createAdminHomeTab('awht', 'admin_workspace');
        self::createAdminHomeTab('dht', 'desktop');
        self::createAdminHomeTab('wht', 'workspace');
        self::createWidget('widget_a', false, false, 'icon');
        self::createWidget('widget_b', false, false, 'icon');
        self::createWidget('widget_c', false, false, 'icon');
        self::createWidget('widget_d', false, false, 'icon');
        self::createAdminWidgetHomeTabConfig(
            'adwhtc_1',
            self::get('widget_a'),
            self::get('adht'),
            true,
            true,
            1
        );
        self::createAdminWidgetHomeTabConfig(
            'adwhtc_2',
            self::get('widget_b'),
            self::get('adht'),
            false,
            false,
            2
        );
        self::createDesktopWidgetHomeTabConfig(
            'uadwhtc_1',
            self::get('widget_b'),
            self::get('adht'),
            self::get('user_a'),
            'admin_desktop',
            true,
            false,
            2
        );
        self::createDesktopWidgetHomeTabConfig(
            'dwhtc_1a',
            self::get('widget_c'),
            self::get('adht'),
            self::get('user_a'),
            'desktop',
            true,
            false,
            1
        );
        self::createDesktopWidgetHomeTabConfig(
            'dwhtc_2a',
            self::get('widget_d'),
            self::get('adht'),
            self::get('user_a'),
            'desktop',
            true,
            false,
            2
        );
        self::createDesktopWidgetHomeTabConfig(
            'dwhtc_1b',
            self::get('widget_a'),
            self::get('dht'),
            self::get('user_a'),
            'desktop',
            true,
            false,
            1
        );
        self::createDesktopWidgetHomeTabConfig(
            'dwhtc_2b',
            self::get('widget_b'),
            self::get('dht'),
            self::get('user_a'),
            'desktop',
            false,
            false,
            2
        );
        self::createDesktopWidgetHomeTabConfig(
            'dwhtc_3b',
            self::get('widget_c'),
            self::get('dht'),
            self::get('user_a'),
            'desktop',
            true,
            false,
            3
        );
        self::createAdminWidgetHomeTabConfig(
            'awwhtc_1',
            self::get('widget_a'),
            self::get('awht'),
            true,
            true,
            1
        );
        self::createAdminWidgetHomeTabConfig(
            'awwhtc_2',
            self::get('widget_b'),
            self::get('awht'),
            true,
            false,
            2
        );
        self::createWorkspaceWidgetHomeTabConfig(
            'wawwhtc_1',
            self::get('widget_a'),
            self::get('awht'),
            self::get('wsa'),
            false,
            false,
            4
        );
        self::createWorkspaceWidgetHomeTabConfig(
            'wawwhtc_2',
            self::get('widget_b'),
            self::get('awht'),
            self::get('wsa'),
            true,
            false,
            3
        );
        self::createWorkspaceWidgetHomeTabConfig(
            'wawwhtc_3',
            self::get('widget_c'),
            self::get('awht'),
            self::get('wsa'),
            true,
            false,
            2
        );
        self::createWorkspaceWidgetHomeTabConfig(
            'wawwhtc_4',
            self::get('widget_d'),
            self::get('awht'),
            self::get('wsa'),
            true,
            false,
            1
        );
        self::createWorkspaceWidgetHomeTabConfig(
            'wwhtc_1',
            self::get('widget_b'),
            self::get('wht'),
            self::get('wsa'),
            false,
            false,
            1
        );
        self::createWorkspaceWidgetHomeTabConfig(
            'wwhtc_2',
            self::get('widget_c'),
            self::get('wht'),
            self::get('wsa'),
            true,
            false,
            2
        );
    }

    public function testFindAdminWidgetConfigs()
    {
        $widgetsHTCs = self::$repo->findAdminWidgetConfigs(self::get('adht'));
        $this->assertEquals(2, count($widgetsHTCs));
        $this->assertEquals(self::get('adwhtc_1'), $widgetsHTCs[0]);
        $this->assertEquals(self::get('adwhtc_2'), $widgetsHTCs[1]);
    }


    public function testFindVisibleAdminWidgetConfigs()
    {
        $widgetsHTCs = self::$repo->findVisibleAdminWidgetConfigs(self::get('adht'));
        $this->assertEquals(1, count($widgetsHTCs));
        $this->assertEquals(self::get('adwhtc_1'), $widgetsHTCs[0]);
    }

    public function testFindWidgetConfigsByUser()
    {
        $widgetsHTCs = self::$repo->findWidgetConfigsByUser(
            self::get('dht'),
            self::get('user_a')
        );
        $this->assertEquals(3, count($widgetsHTCs));
        $this->assertEquals(self::get('dwhtc_1b'), $widgetsHTCs[0]);
        $this->assertEquals(self::get('dwhtc_2b'), $widgetsHTCs[1]);
        $this->assertEquals(self::get('dwhtc_3b'), $widgetsHTCs[2]);
    }


    public function testFindVisibleWidgetConfigsByUser()
    {
        $widgetsHTCs = self::$repo->findVisibleWidgetConfigsByUser(
            self::get('dht'),
            self::get('user_a')
        );
        $this->assertEquals(2, count($widgetsHTCs));
        $this->assertEquals(self::get('dwhtc_1b'), $widgetsHTCs[0]);
        $this->assertEquals(self::get('dwhtc_3b'), $widgetsHTCs[1]);
    }


    public function testFindWidgetConfigsByWorkspace()
    {
        $widgetsHTCs = self::$repo->findWidgetConfigsByWorkspace(
            self::get('awht'),
            self::get('wsa')
        );
        $this->assertEquals(4, count($widgetsHTCs));
        $this->assertEquals(self::get('wawwhtc_4'), $widgetsHTCs[0]);
        $this->assertEquals(self::get('wawwhtc_3'), $widgetsHTCs[1]);
        $this->assertEquals(self::get('wawwhtc_2'), $widgetsHTCs[2]);
        $this->assertEquals(self::get('wawwhtc_1'), $widgetsHTCs[3]);
    }

    public function testFindVisibleWidgetConfigsByWorkspace()
    {
        $widgetsHTCs = self::$repo->findVisibleWidgetConfigsByWorkspace(
            self::get('awht'),
            self::get('wsa')
        );
        $this->assertEquals(3, count($widgetsHTCs));
        $this->assertEquals(self::get('wawwhtc_4'), $widgetsHTCs[0]);
        $this->assertEquals(self::get('wawwhtc_3'), $widgetsHTCs[1]);
        $this->assertEquals(self::get('wawwhtc_2'), $widgetsHTCs[2]);
    }


    public function testFindOrderOfLastWidgetInAdminHomeTab()
    {
        $lastOrder = self::$repo
            ->findOrderOfLastWidgetInAdminHomeTab(self::get('adht'));
        $this->assertEquals(2, $lastOrder['order_max']);
    }


    public function testFindOrderOfLastWidgetInHomeTabByUser()
    {
        $lastOrder = self::$repo->findOrderOfLastWidgetInHomeTabByUser(
            self::get('dht'),
            self::get('user_a')
        );
        $this->assertEquals(3, $lastOrder['order_max']);
    }

    public function testFindOrderOfLastWidgetInHomeTabByWorkspace()
    {
        $lastOrder = self::$repo->findOrderOfLastWidgetInHomeTabByWorkspace(
            self::get('awht'),
            self::get('wsa')
        );
        $this->assertEquals(4, $lastOrder['order_max']);

    }

    public function testUpdateAdminWidgetHomeTabConfig()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }

    public function testUpdateWidgetHomeTabConfigByUser()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }

    public function testUpdateWidgetHomeTabConfigByWorkspace()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }

    public function testUpdateAdminWidgetOrder()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }

    public function testUpdateWidgetOrderByUser()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }

    public function testUpdateWidgetOrderByWorkspace()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }

    public function testFindUserAdminWidgetHomeTabConfig()
    {
        $widgetsHTCs = self::$repo->findUserAdminWidgetHomeTabConfig(
            self::get('adht'),
            self::get('widget_b'),
            self::get('user_a')
        );
        $this->assertEquals(1, count($widgetsHTCs));
        $this->assertEquals(self::get('uadwhtc_1'), $widgetsHTCs[0]);
    }
}