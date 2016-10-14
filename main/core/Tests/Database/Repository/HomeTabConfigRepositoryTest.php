<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class HomeTabConfigRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Home\HomeTabConfig');
        self::createUser('user_a');
        self::createWorkspace('wsa');

        /*
         *  Create admin Home tabs :
         *   _______________________________________________
         *  |  Name  |       Type        | User | Workspace |
         *  |-----------------------------------------------|
         *  | adht_1 | admin_desktop     | NULL |   NULL    |
         *  | adht_2 | admin_desktop     | NULL |   NULL    |
         *  | adht_3 | admin_desktop     | NULL |   NULL    |
         *  | adht_4 | admin_desktop     | NULL |   NULL    |
         *  | awht_1 | workspace_desktop | NULL |   NULL    |
         *  | awht_2 | workspace_desktop | NULL |   NULL    |
         *  | awht_3 | workspace_desktop | NULL |   NULL    |
         *  | awht_4 | workspace_desktop | NULL |   NULL    |
         *  |________|___________________|______|___________|
         */

        self::createAdminHomeTab('adht_1', 'admin_desktop');
        self::createAdminHomeTab('adht_2', 'admin_desktop');
        self::createAdminHomeTab('adht_3', 'admin_desktop');
        self::createAdminHomeTab('adht_4', 'admin_desktop');
        self::createAdminHomeTab('awht_1', 'admin_workspace');
        self::createAdminHomeTab('awht_2', 'admin_workspace');
        self::createAdminHomeTab('awht_3', 'admin_workspace');
        self::createAdminHomeTab('awht_4', 'admin_workspace');

        /*
         *  Create admin Home tab configs :
         *   _____________________________________________________________________________________
         *  |   Name  | HomeTab |       Type        | User | Workspace | Visible | Locked | Order |
         *  |---------|---------|-------------------|------|-----------|---------|--------|-------|
         *  | adhtc_1 | adht_1  | admin_desktop     | NULL |   NULL    |  true   |  true  |   1   |
         *  | adhtc_2 | adht_2  | admin_desktop     | NULL |   NULL    |  true   |  false |   2   |
         *  | adhtc_3 | adht_3  | admin_desktop     | NULL |   NULL    |  false  |  true  |   3   |
         *  | adhtc_4 | adht_4  | admin_desktop     | NULL |   NULL    |  false  |  false |   4   |
         *  | awhtc_1 | awht_1  | workspace_desktop | NULL |   NULL    |  true   |    -   |   1   |
         *  | awhtc_2 | awht_2  | workspace_desktop | NULL |   NULL    |  true   |    -   |   2   |
         *  | awhtc_3 | awht_3  | workspace_desktop | NULL |   NULL    |  false  |    -   |   3   |
         *  | awhtc_4 | awht_4  | workspace_desktop | NULL |   NULL    |  false  |    -   |   4   |
         *  |_________|_________|___________________|______|___________|_________|________|_______|
         */

        self::createAdminHomeTabConfig('adhtc_1', self::get('adht_1'), 'admin_desktop', true, true, 1);
        self::createAdminHomeTabConfig('adhtc_2', self::get('adht_2'), 'admin_desktop', true, false, 2);
        self::createAdminHomeTabConfig('adhtc_3', self::get('adht_3'), 'admin_desktop', false, true, 3);
        self::createAdminHomeTabConfig('adhtc_4', self::get('adht_4'), 'admin_desktop', false, false, 4);
        self::createAdminHomeTabConfig('awhtc_1', self::get('awht_1'), 'admin_workspace', true, false, 1);
        self::createAdminHomeTabConfig('awhtc_2', self::get('awht_2'), 'admin_workspace', true, false, 2);
        self::createAdminHomeTabConfig('awhtc_3', self::get('awht_3'), 'admin_workspace', false, false, 3);
        self::createAdminHomeTabConfig('awhtc_4', self::get('awht_4'), 'admin_workspace', false, false, 4);

        /*
         *  Create Home tabs for user_a :
         *   ______________________________________
         *  | Name  |  Type   |  User  | Workspace |
         *  |-------|---------|--------|-----------|
         *  | dht_1 | desktop | user_a |   NULL    |
         *  | dht_2 | desktop | user_a |   NULL    |
         *  |_______|_________|________|___________|
         */

        self::createDesktopHomeTab('dht_1', self::get('user_a'));
        self::createDesktopHomeTab('dht_2', self::get('user_a'));

        /*
         *  Create Home tabs for wsa :
         *   ________________________________________________
         *  | Name  |   Type    | User | Workspace |
         *  |-------|-----------|------|-----------|
         *  | wht_1 | workspace | NULL |    wsa    |
         *  | wht_2 | workspace | NULL |    wsa    |
         *  |_______|___________|______|___________|
         */

        self::createWorkspaceHomeTab('wht_1', self::get('wsa'));
        self::createWorkspaceHomeTab('wht_2', self::get('wsa'));

        /*
         *  Create Home tab configs for user_a :
         *   ___________________________________________________________________________
         *  |   Name   | HomeTab |     Type      |  User  | Workspace | Visible | Order |
         *  |----------|---------|---------------|--------|-----------|---------|-------|
         *  | dhtc_1   | dht_1   | desktop       | user_a |   NULL    |  true   |   1   |
         *  | dhtc_2   | dht_2   | desktop       | user_a |   NULL    |  false  |   2   |
         *  | uadhtc_2 | adht_2  | admin_desktop | user_a |   NULL    |  true   |   2   |
         *  | uadhtc_4 | adht_4  | admin_desktop | user_a |   NULL    |  false  |   4   |
         *  |__________|_________|_______________|________|___________|_________|_______|
         */

        self::createDesktopHomeTabConfig(
            'dhtc_1',
            self::get('dht_1'),
            self::get('user_a'),
            'desktop',
            true,
            false,
            1
        );
        self::createDesktopHomeTabConfig(
            'dhtc_2',
            self::get('dht_2'),
            self::get('user_a'),
            'desktop',
            false,
            false,
            2
        );
        self::createDesktopHomeTabConfig(
            'uadhtc_2',
            self::get('adht_2'),
            self::get('user_a'),
            'admin_desktop',
            true,
            false,
            2
        );
        self::createDesktopHomeTabConfig(
            'uadhtc_4',
            self::get('adht_4'),
            self::get('user_a'),
            'admin_desktop',
            false,
            false,
            4
        );

        /*
         *  Create Home tab configs for wsa :
         *   ___________________________________________________________________________
         *  |   Name   | HomeTab |      Type       | User | Workspace | Visible | Order |
         *  |----------|---------|-----------------|------|-----------|---------|-------|
         *  | whtc_1   | wht_1   | workspace       | NULL |    wsa    |  true   |   1   |
         *  | whtc_2   | wht_2   | workspace       | NULL |    wsa    |  false  |   2   |
         *  | uawhtc_1 | awhtc_1 | admin_workspace | NULL |    wsa    |  true   |   1   |
         *  | uawhtc_2 | awhtc_2 | admin_workspace | NULL |    wsa    |  false  |   2   |
         *  | uawhtc_3 | awhtc_3 | admin_workspace | NULL |    wsa    |  false  |   3   |
         *  | uawhtc_4 | awhtc_4 | admin_workspace | NULL |    wsa    |  true   |   4   |
         *  |__________|_________|_________________|______|___________|_________|_______|
         */

        self::createWorkspaceHomeTabConfig(
            'whtc_1',
            self::get('wht_1'),
            self::get('wsa'),
            'workspace',
            true,
            false,
            1
        );
        self::createWorkspaceHomeTabConfig(
            'whtc_2',
            self::get('wht_2'),
            self::get('wsa'),
            'workspace',
            false,
            false,
            2
        );
        self::createWorkspaceHomeTabConfig(
            'uawhtc_1',
            self::get('awht_1'),
            self::get('wsa'),
            'admin_workspace',
            true,
            false,
            1
        );
        self::createWorkspaceHomeTabConfig(
            'uawhtc_2',
            self::get('awht_2'),
            self::get('wsa'),
            'admin_workspace',
            false,
            false,
            2
        );
        self::createWorkspaceHomeTabConfig(
            'uawhtc_3',
            self::get('awht_3'),
            self::get('wsa'),
            'admin_workspace',
            false,
            false,
            3
        );
        self::createWorkspaceHomeTabConfig(
            'uawhtc_4',
            self::get('awht_4'),
            self::get('wsa'),
            'admin_workspace',
            true,
            false,
            4
        );
    }

    public function testFindAdminDesktopHomeTabConfigs()
    {
        $homeTabConfigs = self::$repo->findAdminDesktopHomeTabConfigs();
        $this->assertEquals(5, count($homeTabConfigs));
        $this->assertEquals(self::get('adhtc_1'), $homeTabConfigs[1]);
        $this->assertEquals(self::get('adhtc_2'), $homeTabConfigs[2]);
        $this->assertEquals(self::get('adhtc_3'), $homeTabConfigs[3]);
        $this->assertEquals(self::get('adhtc_4'), $homeTabConfigs[4]);
    }

    public function testFindAdminWorkspaceHomeTabConfigs()
    {
        $homeTabConfigs = self::$repo->findAdminWorkspaceHomeTabConfigs();
        $this->assertEquals(5, count($homeTabConfigs));
        $this->assertEquals(self::get('awhtc_1'), $homeTabConfigs[1]);
        $this->assertEquals(self::get('awhtc_2'), $homeTabConfigs[2]);
        $this->assertEquals(self::get('awhtc_3'), $homeTabConfigs[3]);
        $this->assertEquals(self::get('awhtc_4'), $homeTabConfigs[4]);
    }

    public function testFindAdminDesktopHomeTabConfigByHomeTab()
    {
        $homeTabConfig = self::$repo
            ->findAdminDesktopHomeTabConfigByHomeTab(self::get('adht_1'));
        $this->assertEquals(self::get('adhtc_1'), $homeTabConfig);
    }

    public function testFindDesktopHomeTabConfigsByUser()
    {
        $homeTabConfigs = self::$repo
            ->findDesktopHomeTabConfigsByUser(self::get('user_a'));
        $this->assertEquals(2, count($homeTabConfigs));
        $this->assertEquals(self::get('dhtc_1'), $homeTabConfigs[0]);
        $this->assertEquals(self::get('dhtc_2'), $homeTabConfigs[1]);
    }

    public function testFindWorkspaceHomeTabConfigsByWorkspace()
    {
        $homeTabConfigs = self::$repo
            ->findWorkspaceHomeTabConfigsByWorkspace(self::get('wsa'));
        $this->assertEquals(2, count($homeTabConfigs));
        $this->assertEquals(self::get('whtc_1'), $homeTabConfigs[0]);
        $this->assertEquals(self::get('whtc_2'), $homeTabConfigs[1]);
    }

    public function testFindVisibleAdminDesktopHomeTabConfigs()
    {
        $homeTabConfigs = self::$repo->findVisibleAdminDesktopHomeTabConfigs();
        $this->assertEquals(3, count($homeTabConfigs));
        $this->assertEquals(self::get('adhtc_1'), $homeTabConfigs[1]);
        $this->assertEquals(self::get('adhtc_2'), $homeTabConfigs[2]);
    }

    public function testFindVisibleAdminWorkspaceHomeTabConfigs()
    {
        $homeTabConfigs = self::$repo->findVisibleAdminWorkspaceHomeTabConfigs();
        $this->assertEquals(3, count($homeTabConfigs));
        $this->assertEquals(self::get('awhtc_1'), $homeTabConfigs[1]);
        $this->assertEquals(self::get('awhtc_2'), $homeTabConfigs[2]);
    }

    public function testFindVisibleDesktopHomeTabConfigsByUser()
    {
        $homeTabConfigs = self::$repo
            ->findVisibleDesktopHomeTabConfigsByUser(self::get('user_a'));
        $this->assertEquals(1, count($homeTabConfigs));
        $this->assertEquals(self::get('dhtc_1'), $homeTabConfigs[0]);
    }

    public function testFindVisibleWorkspaceHomeTabConfigsByWorkspace()
    {
        $homeTabConfigs = self::$repo
            ->findVisibleWorkspaceHomeTabConfigsByWorkspace(self::get('wsa'));
        $this->assertEquals(1, count($homeTabConfigs));
        $this->assertEquals(self::get('whtc_1'), $homeTabConfigs[0]);
    }

    public function testFindOrderOfLastDesktopHomeTabByUser()
    {
        $lastOrder = self::$repo
            ->findOrderOfLastDesktopHomeTabByUser(self::get('user_a'));
        $this->assertEquals(2, $lastOrder['order_max']);
    }

    public function testFindOrderOfLastWorkspaceHomeTabByWorkspace()
    {
        $lastOrder = self::$repo
            ->findOrderOfLastWorkspaceHomeTabByWorkspace(self::get('wsa'));
        $this->assertEquals(2, $lastOrder['order_max']);
    }

    public function testFindOrderOfLastAdminDesktopHomeTab()
    {
        $lastOrder = self::$repo->findOrderOfLastAdminDesktopHomeTab();
        $this->assertEquals(4, $lastOrder['order_max']);
    }

    public function testFindOrderOfLastAdminWorkspaceHomeTab()
    {
        $lastOrder = self::$repo->findOrderOfLastAdminDesktopHomeTab();
        $this->assertEquals(4, $lastOrder['order_max']);
    }

    public function testUpdateAdminDesktopOrder()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }

    public function testUpdateAdminWorkspaceOrder()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }

    public function testUpdateDesktopOrder()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }

    public function testUpdateWorkspaceOrder()
    {
        $this->markTestSkipped('Cannot retrieve change due to update');
    }
}
