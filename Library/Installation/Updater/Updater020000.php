<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Doctrine\Common\Persistence\Mapping\MappingException;

class Updater020000
{
    private $container;
    private $logger;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function log($message)
    {
        if ($this->logger) {
            $log = $this->logger;
            $log($message);
        }
    }

    public function preUpdate()
    {
        $this->copyWidgetHomeTabConfigTable();
    }

    public function postUpdate()
    {
        $this->initWidgets();
        $this->updateWidgetsDatas();
        $this->updateTextWidgets();
        $this->updateWidgetHomeTabConfigsDatas();
        $this->updateAdminWorkspaceHomeTabDatas();
        $this->createWorkspacesListWidget();
        $this->dropTables();
        $this->dropWidgetHomeTabConfigTableCopy();
    }

    private function initWidgets()
    {
        $this->log('Updating claro_widget table ...');
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $cn->query("UPDATE claro_widget set is_displayable_in_workspace = true,
            is_displayable_in_desktop = true
            WHERE name = 'core_resource_logger'
            OR name = 'simple_text'
            OR name = 'claroline_announcement_widget'
            OR name = 'claroline_rssreader'");

        $cn->query("UPDATE claro_widget set is_displayable_in_workspace = false, is_displayable_in_desktop = true
            WHERE name = 'my_workspaces'");
    }

    private function updateTextWidgets()
    {
        $this->log('Migrating simple text widget data ...');
        $cn = $this->container->get('doctrine.dbal.default_connection');
        //create new table

        //text_widget_id
        $result = $cn->query("SELECT id FROM claro_widget WHERE name = 'simple_text'");
        $widget = $result->fetch();
        $widgetId = $widget['id'];
        $wconfigs = $cn->query("SELECT * FROM simple_text_workspace_widget_config");

        foreach ($wconfigs as $config) {
            if (!$config['is_default']) {
               $query = "INSERT INTO claro_widget_instance (workspace_id, user_id, widget_id, is_admin, is_desktop, name)
                   VALUES ({$config['workspace_id']}, null, {$widgetId}, false, false, 'simple_text' )";
               $cn->query($query);
            }

            $query = "SELECT * FROM claro_widget_instance WHERE workspace_id = {$config['workspace_id']} and widget_id = {$widgetId}";
            $instance = $cn->query($query)->fetch();

            $cn->query("INSERT into claro_simple_text_widget_config (content, widgetInstance_id)
                VALUES (". $cn->quote($config['content']) . ", {$instance['id']})");
        }
    }

    private function updateWidgetsDatas()
    {
        $this->log('Migrating widgets display tables...');
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $select = "SELECT instance. * , widget.name as widget_name
            FROM claro_widget_display instance
            RIGHT JOIN claro_widget widget ON instance.widget_id = widget.id
            WHERE parent_id IS NOT NULL ";

        $datas =  $cn->query($select);

        foreach ($datas as $row) {
           $isAdmin = $row['parent_id'] == NULL ? 'true': 'false';
           $wsId = $row['workspace_id'] ? $row['workspace_id']: 'null';
           $userId = $row['user_id'] ? $row['user_id']: 'null';
           $query = "INSERT INTO claro_widget_instance (workspace_id, user_id, widget_id, is_admin, is_desktop, name)
               VALUES ({$wsId}, {$userId}, {$row['widget_id']}, {$isAdmin}, {$row['is_desktop']}, '{$row['widget_name']}' )";
           $cn->query($query);
        }
    }

    private function dropTables()
    {
        $this->log('Drop useless tables...');
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $cn->query('DROP table claro_widget_display');
        $cn->query('DROP TABLE simple_text_dekstop_widget_config');
        $cn->query('DROP TABLE simple_text_workspace_widget_config');
        $cn->query('DROP TABLE claro_log_workspace_widget_config');
        $cn->query('DROP TABLE claro_log_desktop_widget_config');
    }

    private function updateWidgetHomeTabConfigsDatas()
    {
        $this->log('Updating home tabs...');
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $widgetHomeTabConfigsReq = "
            SELECT *
            FROM claro_widget_home_tab_config_temp
            ORDER BY id
        ";
        $datas =  $cn->query($widgetHomeTabConfigsReq);

        foreach ($datas as $row) {
            $widgetHomeTabConfigId = $row['id'];
            $homeTabId = $row['home_tab_id'];
            $widgetId = $row['widget_id'];

            $homeTabsReq = "
                SELECT *
                FROM claro_home_tab
                WHERE id = {$homeTabId}
            ";
            $homeTab = $cn->query($homeTabsReq)->fetch();
            $homeTabType = $homeTab['type'];

            $widgetInstanceReq = "
                SELECT *
                FROM claro_widget_instance
                WHERE widget_id = {$widgetId}
                AND is_admin = false
            ";

            if ($homeTabType === 'admin_desktop' || $homeTabType === 'desktop') {
                $widgetInstanceReq .= " AND is_desktop = true";
            } else {
                $widgetInstanceReq .= " AND is_desktop = false";
            }

            if (is_null($row['user_id'])) {
                $widgetInstanceReq .= " AND user_id IS NULL";
            } else {
                $widgetInstanceReq .= " AND user_id = {$row['user_id']}";
            }

            if (is_null($row['workspace_id'])) {
                $widgetInstanceReq .= " AND workspace_id IS NULL";
            } else {
                $widgetInstanceReq .= " AND workspace_id = {$row['workspace_id']}";
            }

            $widgetInstances = $cn->query($widgetInstanceReq);
            $widgetInstance = $widgetInstances->fetch();

            if ($widgetInstance) {
                $widgetInstanceId = $widgetInstance['id'];
                $updateReq = "
                    UPDATE claro_widget_home_tab_config
                    SET widget_instance_id = {$widgetInstanceId}
                    WHERE id = {$widgetHomeTabConfigId}
                ";
                $cn->query($updateReq);
            } else {
                $deleteReq = "
                    DELETE FROM claro_widget_home_tab_config
                    WHERE id = {$widgetHomeTabConfigId}
                ";
                $cn->query($deleteReq);
            }
        }
    }

    private function createWorkspacesListWidget()
    {
        $this->log('Writing temporary tables...');
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $workspaceWidget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
                ->findOneByName('my_workspaces');

            if (is_null($workspaceWidget)) {
                $this->logger();
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
        }
        catch (MappingException $e) {
            $this->log('A MappingException has been thrown while trying to get Widget repository');
        }
    }

    private function updateAdminWorkspaceHomeTabDatas()
    {
        $this->log('Updating admin tabs...');
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
                }else {
                    $homeTabConfig->setTabOrder($lastOrder['order_max'] + 1);
                }

                $widgetHomeTabConfigs = $widgetHTCRepo
                    ->findWidgetConfigsByWorkspace($homeTab, $workspace);

                foreach ($widgetHomeTabConfigs as $widgetHomeTabConfig) {
                    $widgetHomeTabConfig->setHomeTab($newHomeTab);
                }
                $em->flush();
            }
        }
        catch (MappingException $e) {
            $this->log('A MappingException has been thrown while trying to get HomeTabConfig or WidgetHomeTabConfig repository');
        }
    }

    private function copyWidgetHomeTabConfigTable()
    {
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $cn->query('
            CREATE TABLE claro_widget_home_tab_config_temp
            AS (SELECT * FROM claro_widget_home_tab_config)
        ');
    }

    private function dropWidgetHomeTabConfigTableCopy()
    {
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $cn->query('DROP TABLE claro_widget_home_tab_config_temp');
    }
}