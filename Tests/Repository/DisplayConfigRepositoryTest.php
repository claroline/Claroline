<?php

namespace Claroline\CoreBundle\Repository;

use Claroline\CoreBundle\Library\Testing\RepositoryTestCase;

class DisplayConfigRepositoryTest extends RepositoryTestCase
{
    private static $repo;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$repo = self::getRepository('ClarolineCoreBundle:Widget\DisplayConfig');
        self::createUser('user_a');
        self::createWorkspace('wsa');
        self::createWidget('widget_a', false, false, 'icon');
        self::createWidget('widget_b', false, false, 'icon');
        self::createWidget('widget_c', false, false, 'icon');
        self::createWidget('widget_d', false, false, 'icon');
        self::createAdminWidgetDisplayConfig(
            'desktop_awdc_a',
            self::get('widget_a'),
            true,
            true,
            true
        );
        self::createAdminWidgetDisplayConfig(
            'desktop_awdc_b',
            self::get('widget_b'),
            false,
            true,
            true
        );
        self::createAdminWidgetDisplayConfig(
            'desktop_awdc_c',
            self::get('widget_c'),
            true,
            false,
            true
        );
        self::createAdminWidgetDisplayConfig(
            'desktop_awdc_d',
            self::get('widget_d'),
            true,
            false,
            true
        );
        self::createAdminWidgetDisplayConfig(
            'workspace_awdc_a',
            self::get('widget_a'),
            true,
            true,
            false
        );
        self::createAdminWidgetDisplayConfig(
            'workspace_awdc_b',
            self::get('widget_b'),
            false,
            true,
            false
        );
        self::createAdminWidgetDisplayConfig(
            'workspace_awdc_c',
            self::get('widget_c'),
            true,
            false,
            false
        );
        self::createAdminWidgetDisplayConfig(
            'workspace_awdc_d',
            self::get('widget_d'),
            true,
            false,
            false
        );
        self::createUserWidgetDisplayConfig(
            self::get('desktop_awdc_a'),
            self::get('widget_a'),
            self::get('user_a'),
            true
        );
        self::createWorkspaceWidgetDisplayConfig(
            self::get('workspace_awdc_a'),
            self::get('widget_a'),
            self::get('wsa'),
            true
        );
    }

    public function testFindVisibleAdminDesktopWidgetDisplayConfig()
    {
        $excludedWidgets = array(self::get('widget_c'));
        $widgetDisplayConfigs =
            self::$repo->findVisibleAdminDesktopWidgetDisplayConfig($excludedWidgets);
        $this->assertEquals(2, count($widgetDisplayConfigs));
        $this->assertEquals(true, $widgetDisplayConfigs[0]->isVisible());
        $this->assertEquals(true, $widgetDisplayConfigs[0]->isDesktop());
        $this->assertEquals(null, $widgetDisplayConfigs[0]->getParent());
        $this->assertEquals(null, $widgetDisplayConfigs[0]->getUser());
        $this->assertEquals(null, $widgetDisplayConfigs[0]->getWorkspace());
        $this->assertEquals(true, $widgetDisplayConfigs[1]->isVisible());
        $this->assertEquals(true, $widgetDisplayConfigs[1]->isDesktop());
        $this->assertEquals(null, $widgetDisplayConfigs[1]->getParent());
        $this->assertEquals(null, $widgetDisplayConfigs[1]->getUser());
        $this->assertEquals(null, $widgetDisplayConfigs[1]->getWorkspace());
    }

    public function testFindVisibleAdminWorkspaceWidgetDisplayConfig()
    {
        $excludedWidgets = array(self::get('widget_c'));
        $widgetDisplayConfigs =
            self::$repo->findVisibleAdminWorkspaceWidgetDisplayConfig($excludedWidgets);
        $this->assertEquals(2, count($widgetDisplayConfigs));
        $this->assertEquals(true, $widgetDisplayConfigs[0]->isVisible());
        $this->assertEquals(false, $widgetDisplayConfigs[0]->isDesktop());
        $this->assertEquals(null, $widgetDisplayConfigs[0]->getParent());
        $this->assertEquals(null, $widgetDisplayConfigs[0]->getUser());
        $this->assertEquals(null, $widgetDisplayConfigs[0]->getWorkspace());
        $this->assertEquals(true, $widgetDisplayConfigs[1]->isVisible());
        $this->assertEquals(false, $widgetDisplayConfigs[1]->isDesktop());
        $this->assertEquals(null, $widgetDisplayConfigs[1]->getParent());
        $this->assertEquals(null, $widgetDisplayConfigs[1]->getUser());
        $this->assertEquals(null, $widgetDisplayConfigs[1]->getWorkspace());
    }
}