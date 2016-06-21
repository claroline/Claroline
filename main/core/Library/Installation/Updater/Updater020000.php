<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\InstallationBundle\Updater\Updater;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Symfony\Component\Filesystem\Filesystem;

class Updater020000 extends Updater
{
    private $container;
    private $conn;
    private $translator;

    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->translator = $container->get('translator');
        $locale = $container->get('claroline.config.platform_config_handler')
            ->getParameter('locale_language');
        $this->translator->setLocale($locale);
    }

    public function preUpdate()
    {
        $this->addLogosAndIcons();
        $this->copyTabConfigTable();
    }

    public function postUpdate()
    {
        // this one isn't specific to 2.0 update
        $this->createWorkspaceListWidget();

        $this->updateWidgetData();
        $this->migrateWorkspaceWidgetData();
        $this->migrateWorkspaceTextWidgetData();
        $this->updateWorkspaceTabData();
        $this->updateWorkspaceAdminTabs();
        $this->reinitializeDesktopTabs();
        $this->updateHomeTool();
        $this->dropTables();
    }

    private function createWorkspaceListWidget()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $workspaceWidget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
                ->findOneByName('my_workspaces');

            if (is_null($workspaceWidget)) {
                $this->log('Creating workspace list widget...');
                $widget = new Widget();
                $widget->setName('my_workspaces');
                $widget->setConfigurable(false);
                $widget->setIcon('fake/icon/path');
                $widget->setPlugin(null);
                $widget->setExportable(false);
                $widget->setDisplayableInDesktop(true);
                $widget->setDisplayableInWorkspace(false);
                $em->persist($widget);
                $em->flush();
            }
        } catch (MappingException $e) {
            $this->log('A MappingException has been thrown while trying to get Widget repository');
        }
    }

    private function addLogosAndIcons()
    {
        $this->log('Dumping logos and icons...');
        $filesystem = new Filesystem();
        $imgDir = __DIR__.'/../../../Resources/public/images';
        $webDir = __DIR__.'/../../../../../../web';
        $filesystem->mirror("{$imgDir}/logos", "{$webDir}/uploads/logos");
        $filesystem->copy("{$imgDir}/ico/favicon.ico", "{$webDir}/favicon.ico", true);
        $filesystem->copy("{$imgDir}/ico/apple-touch-icon.png", "{$webDir}/apple-touch-icon.png", true);
    }

    private function copyTabConfigTable()
    {
        $this->log('Copying tab config table...');
        $this->conn->query('
            CREATE TABLE claro_widget_home_tab_config_temp
            AS (SELECT * FROM claro_widget_home_tab_config)
        ');
    }

    private function updateWidgetData()
    {
        $this->log('Updating widget table data...');
        $this->conn->query("
            UPDATE claro_widget
            SET is_displayable_in_workspace = true,
                is_displayable_in_desktop = true
            WHERE name = 'core_resource_logger'
            OR name = 'simple_text'
            OR name = 'claroline_announcement_widget'
            OR name = 'claroline_rssreader'
        ");
        $this->conn->query("
            UPDATE claro_widget
            SET is_displayable_in_workspace = false,
                is_displayable_in_desktop = true
            WHERE name = 'my_workspaces'
        ");
    }

    private function migrateWorkspaceWidgetData()
    {
        $this->log('Migrating workspace widgets...');
        $select = '
            SELECT instance. * , widget.name AS widget_name
            FROM claro_widget_display instance
            RIGHT JOIN claro_widget widget ON instance.widget_id = widget.id
            WHERE parent_id IS NOT NULL
            AND instance.is_desktop = false
        ';
        $rows = $this->conn->query($select);

        foreach ($rows as $row) {
            $wsId = $row['workspace_id'] ? $row['workspace_id'] : 'null';
            $userId = $row['user_id'] ? $row['user_id'] : 'null';
            $name = $this->conn->quote($this->translator->trans($row['widget_name'], array(), 'widget'));
            $query = "
                INSERT INTO claro_widget_instance (workspace_id, user_id, widget_id, is_admin, is_desktop, name)
                VALUES ({$wsId}, {$userId}, {$row['widget_id']}, false, false, {$name})
            ";
            $this->conn->query($query);
        }
    }

    private function migrateWorkspaceTextWidgetData()
    {
        $this->log('Migrating workspace text widgets configuration...');
        $result = $this->conn->query("SELECT id FROM claro_widget WHERE name = 'simple_text'");
        $widget = $result->fetch();
        $widgetId = $widget['id'];
        $configs = $this->conn->query('SELECT * FROM simple_text_workspace_widget_config');

        foreach ($configs as $config) {
            if (!$config['is_default']) {
                $name = $this->conn->quote($this->translator->trans('simple_text', array(), 'widget'));
                $query = "
                    INSERT INTO claro_widget_instance (workspace_id, user_id, widget_id, is_admin, is_desktop, name)
                    VALUES ({$config['workspace_id']}, null, {$widgetId}, false, false, {$name})
                ";
                $this->conn->query($query);
            }

            $query = "
                SELECT * FROM claro_widget_instance
                WHERE workspace_id = {$config['workspace_id']}
                AND widget_id = {$widgetId}
            ";
            $instance = $this->conn->query($query)->fetch();
            $this->conn->query('
                INSERT INTO claro_simple_text_widget_config (content, widgetInstance_id)
                VALUES ('.$this->conn->quote($config['content']).", {$instance['id']})
            ");
        }
    }

    private function updateWorkspaceTabData()
    {
        $this->log('Updating workspace tabs data...');
        $widgetHomeTabConfigsReq = "
            SELECT *
            FROM claro_widget_home_tab_config_temp
            WHERE type <> 'desktop'
            AND type <> 'admin_desktop'
            ORDER BY id
        ";
        $rows = $this->conn->query($widgetHomeTabConfigsReq);

        foreach ($rows as $row) {
            $widgetHomeTabConfigId = $row['id'];
            $widgetId = $row['widget_id'];
            $widgetInstanceReq = "
                SELECT *
                FROM claro_widget_instance
                WHERE widget_id = {$widgetId}
                AND is_admin = false
            ";

            if (is_null($row['user_id'])) {
                $widgetInstanceReq .= ' AND user_id IS NULL';
            } else {
                $widgetInstanceReq .= " AND user_id = {$row['user_id']}";
            }

            if (is_null($row['workspace_id'])) {
                $widgetInstanceReq .= ' AND workspace_id IS NULL';
            } else {
                $widgetInstanceReq .= " AND workspace_id = {$row['workspace_id']}";
            }

            $widgetInstances = $this->conn->query($widgetInstanceReq);
            $widgetInstance = $widgetInstances->fetch();

            if ($widgetInstance) {
                $widgetInstanceId = $widgetInstance['id'];
                $updateReq = "
                    UPDATE claro_widget_home_tab_config
                    SET widget_instance_id = {$widgetInstanceId}
                    WHERE id = {$widgetHomeTabConfigId}
                ";
                $this->conn->query($updateReq);
            } else {
                $deleteReq = "
                    DELETE FROM claro_widget_home_tab_config
                    WHERE id = {$widgetHomeTabConfigId}
                ";
                $this->conn->query($deleteReq);
            }
        }
    }

    private function updateWorkspaceAdminTabs()
    {
        $this->log('Updating admin workspace tabs...');
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $homeTabConfigRepo = $em->getRepository('ClarolineCoreBundle:Home\HomeTabConfig');
            $widgetHTCRepo = $em->getRepository('ClarolineCoreBundle:Widget\WidgetHomeTabConfig');

            $homeTabConfigs = $homeTabConfigRepo->findWorkspaceHomeTabConfigsByAdmin();

            foreach ($homeTabConfigs as $homeTabConfig) {
                $homeTab = $homeTabConfig->getHomeTab();
                $workspace = $homeTabConfig->getWorkspace();

                $newHomeTab = new HomeTab();
                $newHomeTab->setType('workspace');
                $newHomeTab->setWorkspace($workspace);
                $newHomeTab->setName($homeTab->getName());
                $em->persist($newHomeTab);
                $em->flush();

                $homeTabConfig->setType('workspace');
                $homeTabConfig->setHomeTab($newHomeTab);
                $lastOrder = $homeTabConfigRepo
                    ->findOrderOfLastWorkspaceHomeTabByWorkspace($workspace);

                if (is_null($lastOrder['order_max'])) {
                    $homeTabConfig->setTabOrder(1);
                } else {
                    $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
                }

                $widgetHomeTabConfigs = $widgetHTCRepo
                    ->findWidgetConfigsByWorkspace($homeTab, $workspace);

                foreach ($widgetHomeTabConfigs as $widgetHomeTabConfig) {
                    $widgetHomeTabConfig->setHomeTab($newHomeTab);
                }
                $em->flush();
            }
        } catch (MappingException $e) {
            $this->log(
                'A MappingException has been thrown while trying to get HomeTabConfig'
                .' or WidgetHomeTabConfig repository'
            );
        }
    }

    private function reinitializeDesktopTabs()
    {
        $this->log('Reinitializing desktop tabs...');

        $this->conn->query("
            DELETE FROM claro_home_tab
            WHERE type IN ('desktop', 'admin_desktop')
        ");

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->clear(); // weird but needed...
        $widgetRepo = $em->getRepository('ClarolineCoreBundle:Widget\Widget');
        $widgets = array('claroline_announcement_widget', 'my_workspaces');
        $users = $em->getRepository('ClarolineCoreBundle:User')->findAll();

        foreach ($users as $user) {
            $tab = new HomeTab();
            $tab->setName('Informations');
            $tab->setType('desktop');
            $tab->setUser($user);
            $em->persist($tab);

            $tabConfig = new HomeTabConfig();
            $tabConfig->setHomeTab($tab);
            $tabConfig->setType('desktop');
            $tabConfig->setTabOrder(1);
            $tabConfig->setUser($user);
            $em->persist($tabConfig);

            for ($i = 0, $count = count($widgets); $i < $count; ++$i) {
                $widget = $widgetRepo->findOneByName($widgets[$i]);
                $instance = new WidgetInstance($widget);
                $instance->setName($this->translator->trans($widget->getName(), array(), 'widget'));
                $instance->setIsAdmin(false);
                $instance->setIsDesktop(true);
                $instance->setWidget($widget);
                $instance->setUser($user);
                $em->persist($instance);

                $widgetTabConfig = new WidgetHomeTabConfig();
                $widgetTabConfig->setType('desktop');
                $widgetTabConfig->setHomeTab($tab);
                $widgetTabConfig->setWidgetInstance($instance);
                $widgetTabConfig->setWidgetOrder($i + 1);
                $widgetTabConfig->setUser($user);
                $em->persist($widgetTabConfig);
            }
        }

        $em->flush();
    }

    private function updateHomeTool()
    {
        $this->log('Updating home tool...');
        $this->conn->query("
            UPDATE claro_tools
            SET is_configurable_in_workspace = false,
            is_configurable_in_desktop = false
            WHERE name = 'home'
        ");
    }

    private function dropTables()
    {
        $this->log('Dropping outdated and temporary tables...');
        $this->conn->query('DROP table claro_widget_display');
        $this->conn->query('DROP TABLE simple_text_dekstop_widget_config');
        $this->conn->query('DROP TABLE simple_text_workspace_widget_config');
        $this->conn->query('DROP TABLE claro_log_workspace_widget_config');
        $this->conn->query('DROP TABLE claro_log_desktop_widget_config');
        $this->conn->query('DROP TABLE claro_widget_home_tab_config_temp');
    }
}
