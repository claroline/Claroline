<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Claroline\CoreBundle\DataFixtures\Required\LoadResourceTypeData;
use Claroline\CoreBundle\DataFixtures\Required\LoadPlatformRolesData;
use Claroline\CoreBundle\DataFixtures\Required\LoadToolsData;
use Claroline\CoreBundle\Entity\User;

class Updater002000005
{
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function preUpdate()
    {
//        $this->addFixtureDebug();
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
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $cn->query("UPDATE claro_widget set is_displayable_in_workspace = true, is_displayable_in_desktop = true
            WHERE name = 'core_resource_logger' or name = 'simple_text' or name = 'claroline_announcement_widget'
            or name = 'claroline_rssreader'");

        $cn->query("UPDATE claro_widget set is_displayable_in_workspace = false, is_displayable_in_desktop = true
            WHERE name = 'my_workspaces'");
    }

    private function updateTextWidgets()
    {
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
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $select = "SELECT instance.* FROM claro_widget_display instance where parent_id is not null
            ORDER BY id";
        $datas =  $cn->query($select);

        foreach ($datas as $row) {
           $isAdmin = $row['parent_id'] == NULL ? 'true': 'false';
           $wsId = $row['workspace_id'] ? $row['workspace_id']: 'null';
           $userId = $row['user_id'] ? $row['user_id']: 'null';
           $query = "INSERT INTO claro_widget_instance (workspace_id, user_id, widget_id, is_admin, is_desktop, name)
               VALUES ({$wsId}, {$userId}, {$row['widget_id']}, {$isAdmin}, {$row['is_desktop']}, 'nom' )";
           $cn->query($query);
        }
    }

    private function dropTables()
    {
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $cn->query('DROP table claro_widget_display');
        $cn->query('DROP TABLE simple_text_dekstop_widget_config');
        $cn->query('DROP TABLE simple_text_workspace_widget_config');
        $cn->query('DROP TABLE claro_log_workspace_widget_config');
        $cn->query('DROP TABLE claro_log_desktop_widget_config');
    }

    private function updateWidgetHomeTabConfigsDatas()
    {
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
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $workspaceWidget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
                ->findOneByName('my_workspaces');

            if (is_null($workspaceWidget)) {
                echo 'Creating workspaces list widget...';
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
            echo 'A MappingException has been thrown while trying to get Widget repository';
        }
    }

    private function updateAdminWorkspaceHomeTabDatas()
    {
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
            echo 'A MappingException has been thrown while trying to get HomeTabConfig or WidgetHomeTabConfig repository';
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

    private function addFixtureDebug()
    {
        //these lines are usefull for debugging
        $cn = $this->container->get('doctrine.dbal.default_connection');

        //resource types
        $fixture = new LoadResourceTypeData();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->container);
        $fixture->load($em);

        //roles
        $fixture = new LoadPlatformRolesData();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->container);
        $fixture->load($em);

        //tools
        $fixture = new LoadToolsData();
        $em = $this->container->get('doctrine.orm.entity_manager');
        $referenceRepo = new ReferenceRepository($em);
        $fixture->setReferenceRepository($referenceRepo);
        $fixture->setContainer($this->container);
        $fixture->load($em);

        //widgets
        $cn->query('INSERT INTO claro_widget (plugin_id, name, is_configurable, icon, is_exportable)
            VALUES (null, "simple_text", 1, "fake/path", 0)');
        $cn->query('
            INSERT INTO claro_widget (plugin_id, name, is_configurable, icon, is_exportable)
            VALUES (null, "my_test", 0, "fake/path", 0)
        ');

        //user
        $user = new User();
        $user->setUsername('root');
        $user->setFirstName('root');
        $user->setLastName('root');
        $user->setAdministrativeCode('root');
        $user->setMail('roo@t.root');
        $this->container->get('claroline.manager.user_manager')->createUser($user);
        $em->flush();

        // For simple_text widget
        $cn->query('INSERT INTO claro_widget_display (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (null, null, null, 1, 1, 1, 0)');
        $cn->query('INSERT INTO claro_widget_display (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (1, 1, null, 1, 1, 1, 0)');
        $cn->query('INSERT INTO claro_widget_display (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (null, null, null, 1, 1, 1, 1)');
        $cn->query('INSERT INTO claro_widget_display (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (3, null, 1, 1, 1, 1, 1)');

        // For my_test widget
        $cn->query('
            INSERT INTO claro_widget_display
            (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (null, null, null, 2, 1, 1, 0)
        ');
        $cn->query('
            INSERT INTO claro_widget_display
            (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (null, null, null, 2, 1, 1, 1)
        ');
        $cn->query('
            INSERT INTO claro_widget_display
            (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (5, 1, null, 2, 1, 1, 0)
        ');
        $cn->query('
            INSERT INTO claro_widget_display
            (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (6, null, 1, 2, 1, 1, 1)
        ');

        $cn->query('INSERT INTO simple_text_dekstop_widget_config
            (user_id, is_default, content)
            VALUES (null, true, "dadmin_default")'
        );
        $cn->query('INSERT INTO simple_text_dekstop_widget_config
            (user_id, is_default, content)
            VALUES (1, false, "duser_default")'
        );
        $cn->query('INSERT INTO simple_text_workspace_widget_config
            (workspace_id, is_default, content)
            VALUES (null, true, "wadmin_default")'
        );
        $cn->query('INSERT INTO simple_text_workspace_widget_config
            (workspace_id, is_default, content)
            VALUES (1, false, "wuser_default")'
        );

        /*#############
         #  Home tabs #
         #############*/
        // admin desktop
        $cn->query('
            INSERT INTO claro_home_tab (name, type)
            VALUES ("Informations", "admin_desktop")
        ');
        // admin workspace
        $cn->query('
            INSERT INTO claro_home_tab (name, type)
            VALUES ("Informations", "admin_workspace")
        ');
        // desktop
        $cn->query('
            INSERT INTO claro_home_tab (user_id, name, type)
            VALUES (1, "Desktop tab", "desktop")
        ');
        // workspace
        $cn->query('
            INSERT INTO claro_home_tab (workspace_id, name, type)
            VALUES (1, "Workspace Tab", "workspace")
        ');

        /*#####################
         #  Home tabs configs #
         #####################*/
        // admin desktop
        $cn->query('
            INSERT INTO claro_home_tab_config
            (home_tab_id, type, is_visible, is_locked, tab_order)
            VALUES (1, "admin_desktop", true, false, 1)
        ');
        // admin workspace
        $cn->query('
            INSERT INTO claro_home_tab_config
            (home_tab_id, type, is_visible, is_locked, tab_order)
            VALUES (2, "admin_workspace", true, false, 1)
        ');
        // admin desktop -> user
        $cn->query('
            INSERT INTO claro_home_tab_config
            (home_tab_id, user_id, type, is_visible, is_locked, tab_order)
            VALUES (1, 1, "admin_desktop", true, false, 1)
        ');
        // admin workspace -> workspace
        $cn->query('
            INSERT INTO claro_home_tab_config
            (home_tab_id, workspace_id, type, is_visible, is_locked, tab_order)
            VALUES (2, 1, "admin_workspace", true, false, 1)
        ');
        // desktop
        $cn->query('
            INSERT INTO claro_home_tab_config
            (home_tab_id, user_id, type, is_visible, is_locked, tab_order)
            VALUES (3, 1, "desktop", true, false, 1)
        ');
        // workspace
        $cn->query('
            INSERT INTO claro_home_tab_config
            (home_tab_id, workspace_id, type, is_visible, is_locked, tab_order)
            VALUES (4, 1, "workspace", true, false, 1)
        ');

        /*###########################
         # Widget Home tabs configs #
         ###########################*/
        // admin desktop
        $cn->query('
            INSERT INTO claro_widget_home_tab_config
            (widget_id, home_tab_id, type, is_visible, is_locked, widget_order)
            VALUES (1, 1, "admin", true, false, 1)
        ');
        // admin workspace
        $cn->query('
            INSERT INTO claro_widget_home_tab_config
            (widget_id, home_tab_id, type, is_visible, is_locked, widget_order)
            VALUES (1, 2, "admin", true, false, 1)
        ');
        // admin desktop -> admin widget
        $cn->query('
            INSERT INTO claro_widget_home_tab_config
            (widget_id, home_tab_id, user_id, type, is_visible, is_locked, widget_order)
            VALUES (1, 1, 1, "admin_desktop", true, false, 1)
        ');
        // admin desktop -> user widget
        $cn->query('
            INSERT INTO claro_widget_home_tab_config
            (widget_id, home_tab_id, user_id, type, is_visible, is_locked, widget_order)
            VALUES (2, 1, 1, "desktop", true, false, 1)
        ');
        // admin workspace -> admin widget
        $cn->query('
            INSERT INTO claro_widget_home_tab_config
            (widget_id, home_tab_id, workspace_id, type, is_visible, is_locked, widget_order)
            VALUES (1, 2, 1, "workspace", true, false, 1)
        ');
        // desktop -> desktop widget
        $cn->query('
            INSERT INTO claro_widget_home_tab_config
            (widget_id, home_tab_id, user_id, type, is_visible, is_locked, widget_order)
            VALUES (1, 3, 1, "desktop", true, false, 1)
        ');
        // workspace -> workspace widget
        $cn->query('
            INSERT INTO claro_widget_home_tab_config
            (widget_id, home_tab_id, workspace_id, type, is_visible, is_locked, widget_order)
            VALUES (1, 4, 1, "workspace", true, false, 1)
        ');
    }
}