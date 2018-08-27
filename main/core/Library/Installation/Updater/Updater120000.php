<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\Type\SimpleWidget;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Entity\Widget\WidgetContainerConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstanceConfig;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Updater120000 extends Updater
{
    protected $logger;

    /** @var ObjectManager */
    private $om;

    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;

        $this->om = $container->get('claroline.persistence.object_manager');
        $this->conn = $container->get('doctrine.dbal.default_connection');
        $this->config = $container->get('claroline.config.platform_config_handler');
        $this->container = $container;
    }

    public function preUpdate()
    {
        $this->saveOldTabsTables();
    }

    public function saveOldTabsTables()
    {
        $tableManager = $this->container->get('claroline.persistence.table_manager');
        $tableManager->setLogger($this->logger);

        $toCopy = [
          'claro_home_tab',
          'claro_widget_instance',
          'claro_widget_display_config',
          'claro_widget_home_tab_config',
          'claro_simple_text_widget_config',
          'claro_widget',
        ];

        foreach ($toCopy as $table) {
            $tableManager->copy($table);
        }
    }

    public function updateTabsStructure()
    {
        $this->log('Restoring HomeTabConfigs...');

        $sql = '
            UPDATE claro_home_tab_config config
            LEFT JOIN claro_home_tab_temp tab
            ON config.home_tab_id = tab.id
            SET config.name = tab.name, config.longTitle = tab.name, config.centerTitle = false';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    public function postUpdate()
    {
        $this->updatePlatformParameters();

        $this->updateHomeTabType();
        $this->removeTool('parameters');
        $this->removeTool('claroline_activity_tool');
        $this->updateTabsStructure();
        $this->updateWidgetsStructure();
        $this->restoreWidgetInstancesConfigs();
        $this->checkDesktopTabs();
        $this->updateWidgetInstanceConfigType();
        $this->deactivateActivityResourceType();
    }

    private function updateHomeTabType()
    {
        $this->log('Updating hometab types...');
        $sql = 'UPDATE claro_home_tab SET type = "administration" WHERE type LIKE "admin_desktop" OR type LIKE "admin"';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    private function updateWidgetInstanceConfigType()
    {
        $this->log('Updating hometab types...');
        $sql = 'UPDATE claro_widget_instance_config SET type = "administration" WHERE type LIKE "admin_desktop" OR type LIKE "admin"';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    private function updatePlatformParameters()
    {
        $oldName = 'default_root_anon_id';
        $newName = 'authorized_ips_username';

        if ($this->config->hasParameter($oldName)) {
            // param not already changed
            $this->log(
                sprintf('Renaming platform parameter `%s` into `%s`.', $oldName, $newName)
            );

            $userName = null;

            $userId = $this->config->getParameter($oldName);
            if (!empty($userId)) {
                // load corresponding entity

                /** @var User $user */
                $user = $this->om->getRepository('ClarolineCoreBundle:User')->find($userId);
                if (!empty($user)) {
                    $userName = $user->getUsername();
                }
            }

            $this->config->setParameter($newName, $userName);
            $this->config->removeParameter($oldName);
        }
    }

    private function removeTool($toolName)
    {
        $this->log(sprintf('Removing `%s` tool...', $toolName));

        $tool = $this->om->getRepository('ClarolineCoreBundle:Tool\Tool')->findOneBy(['name' => $toolName]);
        if (!empty($tool)) {
            $this->om->remove($tool);
            $this->om->flush();
        }
    }

    private function updateWidgetsStructure()
    {
        $this->log('Update widget structure...');

        if (count($this->om->getRepository(WidgetContainer::class)->findAll()) > 0) {
            $this->log('Containers already migrated. Truncate manually to try again.');
        } else {
            $this->log('WidgetContainer migration.');
            $sql = '
                INSERT INTO claro_widget_container (id, uuid)
                SELECT id, (SELECT UUID()) as uuid FROM claro_widget_display_config_temp';

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }

        if (count($this->om->getRepository(WidgetContainerConfig::class)->findAll()) > 0) {
            $this->log('Containers Config already migrated. Truncate manually to try again.');
        } else {
            $this->log('WidgetContainerConfig migration.');
            $sql = "
                INSERT INTO claro_widget_container_config (id, uuid, widget_name, color, backgroundType, background, position, layout, widget_container_id)
                SELECT container.id, (SELECT UUID()) as uuid, instance_temp.name, temp.color, 'none', temp.color, '0', '[1]', container.id
                FROM claro_widget_container container
                LEFT JOIN claro_widget_instance instance ON instance.container_id = container.id
                LEFT JOIN claro_widget_instance_temp instance_temp ON instance_temp.id = instance.id
                LEFT JOIN claro_widget_display_config_temp temp ON temp.widget_instance_id = instance_temp.id";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }

        if (count($this->om->getRepository(SimpleWidget::class)->findAll()) > 0) {
            $this->log('SimpleTextWidget already migrated');
        } else {
            $this->log('Migrating SimpleTextWidget to SimpleWidget...');

            $widget = $this->om->getRepository(Widget::class)->findOneBy(['name' => 'simple']);

            $sql = "
                INSERT INTO claro_widget_instance (id, widget_id, uuid)
                SELECT instance_temp.id, {$widget->getId()}, (SELECT UUID()) as uuid from claro_widget_instance_temp instance_temp
                JOIN claro_widget_temp widget_temp ON instance_temp.widget_id = widget_temp.id
                WHERE widget_temp.name = 'simple_text'
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $sql = '
              INSERT INTO claro_widget_simple (id, content, widgetInstance_id)
              SELECT id, content, widgetInstance_id from claro_simple_text_widget_config_temp';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        }

        $this->log('Link instances to container.');

        $sql =
          '
            UPDATE claro_widget_instance instance
            LEFT JOIN claro_widget_display_config_temp wdc ON wdc.widget_instance_id = instance.id
            LEFT JOIN claro_widget_container container ON container.id = wdc.id
            SET instance.container_id = container.id
          ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $this->log('Link container to home tab.');
        $sql =
          '
            UPDATE claro_widget_container container
            LEFT JOIN claro_widget_display_config_temp wtc ON wtc.id = container.id
            LEFT JOIN claro_widget_home_tab_config_temp htc ON wtc.widget_instance_id = htc.widget_instance_id
            SET container.homeTab_id = htc.home_tab_id
          ';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
    }

    private function checkDesktopTabs()
    {
        $tabs = $this->om->getRepository(HomeTab::class)->findBy(['type' => HomeTab::TYPE_ADMIN_DESKTOP]);

        if (0 === count($tabs)) {
            $this->log('Adding default admin desktop tab...');

            $desktopHomeTab = new HomeTab();
            $desktopHomeTab->setType('admin_desktop');
            $desktopHomeTab->setName('Accueil');
            $desktopHomeTab->setLongTitle('Accueil');
            $this->om->persist($desktopHomeTab);

            $desktopHomeTabConfig = new HomeTabConfig();
            $desktopHomeTabConfig->setHomeTab($desktopHomeTab);
            $desktopHomeTabConfig->setType('admin_desktop');
            $desktopHomeTabConfig->setVisible(true);
            $desktopHomeTabConfig->setLocked(false);
            $desktopHomeTabConfig->setTabOrder(1);

            $this->om->persist($desktopHomeTabConfig);
            $this->om->flush();
        }
    }

    private function restoreWidgetInstancesConfigs()
    {
        if (0 === $this->om->count(WidgetInstanceConfig::class)) {
            $this->log('Copying WidgetInsanceConfigs');

            $sql = '
                INSERT INTO claro_widget_instance_config (id, widget_instance_id, workspace_id, widget_order, type, is_visible, is_locked)
                SELECT temp.id, temp.widget_instance_id, temp.workspace_id, temp.widget_order, temp.type, temp.is_visible, temp.is_locked from claro_widget_home_tab_config_temp temp
                JOIN claro_widget_instance instance on instance.id = temp.widget_instance_id
            ';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $sql = 'UPDATE claro_widget_instance_config set widget_order = 0';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
        } else {
            $this->log('WidgetInstanceConfigs already copied');
        }
    }

    private function deactivateActivityResourceType()
    {
        $resourceTypeRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\ResourceType');
        $activityType = $resourceTypeRepo->findOneBy(['name' => 'activity']);

        if (!empty($activityType)) {
            $this->log('Deactivating Activity resource...');

            $activityType->setEnabled(false);
            $this->om->persist($activityType);
            $this->om->flush();

            $this->log('Activity resource is deactivated.');
        }
    }
}
