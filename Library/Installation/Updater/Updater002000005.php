<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

class Updater002000005
{
    private $container;
    
    public function __construct($container)
    {
        $this->container = $container;
    }

    public function preUpdate()
    {
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $cn->query('TRUNCATE table claro_widget_home_tab_config');
        $cn->query('TRUNCATE table claro_home_tab_config');
        $cn->query('TRUNCATE table claro_home_tab');
    }
    public function postUpdate()
    {
        $this->saveTextConfigs();
        $this->updateWidgetsDatas();
    }
    
    private function saveTextConfigs()
    {
        $cn = $this->container->get('doctrine.dbal.default_connection');
        //create new table
        $create = "
            CREATE TABLE save_simple_text_dekstop_widget_config (
                id INT AUTO_INCREMENT NOT NULL, 
                user_id INT DEFAULT NULL, 
                workspace_id INT DEFAULT NULL, 
                is_default TINYINT(1) NOT NULL, 
                content LONGTEXT NOT NULL, 
                INDEX IDX_BAB9695A76ED395 (user_id), 
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB
        ";
        $cn->query($create);
        
        $dconfigs = "SELECT * FROM simple_text_dekstop_widget_config";
        
        foreach ($dconfigs as $config) {
           $query = "INSERT INTO save_simple_text_dekstop_widget_config (workspace_id, user_id, is_default, content)
               VALUES (null, {$config['user_id']}, {$config['is_default']}, {$config['content']})";
           $cn->query($query);
        }
        
        $wconfigs = "SELECT * FROM simple_text_dekstop_widget_config";
        
        foreach ($wconfigs as $config) {
           $query = "INSERT INTO save_simple_text_dekstop_widget_config (workspace_id, user_id, is_default, content)
               VALUES ({$config['workspace_id']}, null, {$config['is_default']}, {$config['content']})";
           $cn->query($query);
        }
        
        $cn->query('DROP TABLE simple_text_dekstop_widget_config');
        $cn->query('DROP TABLE simple_text_workspace_widget_config');
    }
    
    private function updateWidgetsDatas()
    {  
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $select = "SELECT * FROM claro_widget_display";
        $datas =  $cn->query($select);

        foreach ($datas as $row) {
           $isAdmin = $row['parent_id'] == NULL ? true: false;
           $wsId = $row['workspace_id'] ? $row['workspace_id']: 'null';
           $userId = $row['user_id'] ? $row['user_id']: 'null';
           $query = "INSERT INTO claro_widget_instance (workspace_id, user_id, widget_id, is_admin, is_desktop, name)
           VALUES ({$wsId}, {$userId}, {$row['widget_id']}, {$isAdmin}, {$row['is_desktop']}, 'change me !')";
           $cn->query($query);
        }
    }
    
    private function createWorkspacesListWidget()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        try {
            $workspaceWidget = $em->getRepository('ClarolineCoreBundle:Widget\Widget')
                ->findOneByName('my_workspaces');

            if (is_null($workspaceWidget)) {
                $this->log('Creating workspaces list widget...');
                $widget = new Widget();
                $widget->setName('my_workspaces');
                $widget->setConfigurable(false);
                $widget->setIcon('fake/icon/path');
                $widget->setPlugin(null);
                $widget->setExportable(false);
                $em->persist($widget);
                $em->flush();

                $widgetConfig = new WidgetInstance();
                $widgetConfig->setWidget($widget);
                $widgetConfig->setLock(false);
                $widgetConfig->setVisible(true);
                $widgetConfig->setParent(null);
                $widgetConfig->setDesktop(true);

                $em->persist($widgetConfig);
                $em->flush();
            }
        }
        catch (MappingException $e) {
            $this->log('A MappingException has been thrown while trying to get Widget repository');
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
                $workspace = $homeTabConfig->getWorspace();

                $newHomeTab = new HomeTab();
                $newHomeTab->setType('workspace');
                $newHomeTab->setWorkspace($workspace);
                $newHomeTab->setName($homeTab->getName());
                $em->persist($newHomeTab);
                $em->flush();

                $homeTabConfig->setType('workspace');
                $homeTabConfig->setHomeTab($newHomeTab);

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
}