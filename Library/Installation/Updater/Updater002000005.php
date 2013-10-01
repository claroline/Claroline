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
        $this->addFixtureDebug();
    }
    
    public function postUpdate()
    {
        $this->updateWidgetsDatas();
        $this->updateTextWidgets();
        $this->dropTables();
    }
    
    private function updateTextWidgets()
    {
        $cn = $this->container->get('doctrine.dbal.default_connection');
        //create new table
        
        $dconfigs = $cn->query("SELECT * FROM simple_text_dekstop_widget_config");
        //text_widget_id
        $result = $cn->query("SELECT id FROM claro_widget WHERE name = 'simple_text'");
        $widget = $result->fetch();
        $widgetId = $widget['id'];
        
        foreach ($dconfigs as $config) {
            //find the correct widget.
            if ($config['is_default']) {
                $result = $cn->query("SELECT id FROM claro_widget_instance where is_desktop = true and is_admin = true and widget_id = {$widgetId}");
            } else {
                $result = $cn->query("SELECT id FROM claro_widget_instance where user_id = {$config['user_id']} and widget_id = {$widgetId}");
            }
            
            $instance = $result->fetch();
            
            $cn->query("INSERT into claro_simple_text_widget_config (content, widgetInstance_id)
                VALUES ('{$config['content']}', {$instance['id']})");
        }
        
        $wconfigs = $cn->query("SELECT * FROM simple_text_workspace_widget_config");
        
        foreach ($wconfigs as $config) {
            if ($config['is_default']) {
                $result = $cn->query("SELECT id FROM claro_widget_instance where is_desktop = false and is_admin = true and widget_id = {$widgetId}");
            } else {
                $result = $cn->query("SELECT id FROM claro_widget_instance where workspace_id = {$config['workspace_id']} and widget_id = {$widgetId} and is_admin = false");
            }
            
            $instance = $result->fetch();
            
            $cn->query("INSERT into claro_simple_text_widget_config (content, widgetInstance_id)
                VALUES ('{$config['content']}', {$instance['id']})");
        }
    }
    
    private function updateWidgetsDatas()
    {  
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $select = "SELECT * FROM claro_widget_display ORDER BY id";
        $datas =  $cn->query($select);

        foreach ($datas as $row) {
           $isAdmin = $row['parent_id'] == NULL ? 'true': 'false';
           $wsId = $row['workspace_id'] ? $row['workspace_id']: 'null';
           $userId = $row['user_id'] ? $row['user_id']: 'null';
           $query = "INSERT INTO claro_widget_instance (workspace_id, user_id, widget_id, is_admin, is_desktop, name)
               VALUES ({$wsId}, {$userId}, {$row['widget_id']}, {$isAdmin}, {$row['is_desktop']}, 'change me !')";
           $cn->query($query);
        }
    }
   
    private function dropTables()
    {
        $cn = $this->container->get('doctrine.dbal.default_connection');
        $cn->query('DROP table claro_widget_display');
        $cn->query('DROP TABLE simple_text_dekstop_widget_config');
        $cn->query('DROP TABLE simple_text_workspace_widget_config');
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
       
        //user
        $user = new User();
        $user->setUsername('root');
        $user->setFirstName('root');
        $user->setLastName('root');
        $user->setAdministrativeCode('root');
        $user->setMail('roo@t.root');
        $this->container->get('claroline.manager.user_manager')->createUser($user);
        $em->flush();
        
        $cn->query('INSERT INTO claro_widget_display (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (null, null, null, 1, 1, 1, 0)');
        $cn->query('INSERT INTO claro_widget_display (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (1, 1, null, 1, 1, 1, 0)');
        $cn->query('INSERT INTO claro_widget_display (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (null, null, null, 1, 1, 1, 1)');
        $cn->query('INSERT INTO claro_widget_display (parent_id, workspace_id, user_id, widget_id, is_locked, is_visible, is_desktop)
            VALUES (3, null, 1, 1, 1, 1, 1)');
        
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
    }
}