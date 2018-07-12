<?php

namespace Claroline\CoreBundle\Library\Installation\Updater;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\Type\SimpleWidget;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
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
    }

    public function postUpdate()
    {
        $this->updatePlatformParameters();

        $this->removeTool('parameters');
        $this->removeTool('claroline_activity_tool');
        $this->updateWidgetsStructure();
        $this->checkDesktopTabs();
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
            $this->log('WidgetContainer already migrated');
        } else {
            $this->log('Migrating WidgetDisplayConfig to WidgetContainer');

            $sql = 'SELECT * FROM claro_widget_display_config ';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $i = 0;

            foreach ($stmt->fetchAll() as $rowConfig) {
                $this->restoreWidgetContainer($rowConfig);
                ++$i;

                if (0 === $i % 200) {
                    $this->om->flush();
                }
            }

            $this->om->flush();
        }

        if (count($this->om->getRepository(SimpleWidget::class)->findAll()) > 0) {
            $this->log('SimpleTextWidget already migrated');
        } else {
            $this->log('Migrating SimpleTextWidget to SimpleWidget...');

            $sql = 'SELECT * FROM claro_simple_text_widget_config ';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $i = 0;

            foreach ($stmt->fetchAll() as $rowConfig) {
                $this->restoreTextConfig($rowConfig);
                ++$i;

                if (0 === $i % 200) {
                    $this->om->flush();
                }
            }

            $this->om->flush();
        }

        $this->log('Updating HomeTabs titles...');

        $tabs = $this->om->getRepository(HomeTab::class)->findAll();
        $i = 0;

        foreach ($tabs as $tab) {
            if (!$tab->getLongTitle() || !$tab->getName()) {
                $this->updateTabTitle($tab);
            }

            ++$i;

            if (0 === $i % 200) {
                $this->om->flush();
            }
        }

        $this->om->flush();
    }

    private function restoreWidgetContainer($row)
    {
        $widgetContainer = new WidgetContainer();
        $widgetInstance = $this->om->getRepository(WidgetInstance::class)->find($row['widget_instance_id']);
        $this->log('migrating '.$widgetInstance->getName().' ...');
        $widgetContainer->addInstance($widgetInstance);
        $widgetContainer->setBackground($row['color']);
        $widgetContainer->setName($widgetInstance->getName());
        $widgetContainer->setLayout([1]);

        $this->om->persist($widgetContainer);
    }

    private function restoreTextConfig($row)
    {
        $simpleWidget = new SimpleWidget();
        $simpleWidget->setContent($row['content']);
        $widgetInstance = $this->om->getRepository(WidgetInstance::class)->find($row['widgetInstance_id']);
        $this->log('migrating content of default'.$widgetInstance->getName().' ...');
        $simpleWidget->setWidgetInstance($widgetInstance);
        $widget = $this->om->getRepository(Widget::class)->findOneBy(['name' => 'simple']);
        $widgetInstance->setWidget($widget);
        $this->om->persist($widgetInstance);
        $this->om->persist($simpleWidget);
    }

    private function updateTabTitle(HomeTab $tab)
    {
        $this->log('Renaming tab '.$tab->getName().'...');

        if ('' === trim(strip_tags($tab->getName()))) {
            $tab->setName('Unknown');
        }

        if (!$tab->getLongTitle()) {
            $tab->setLongTitle(strip_tags($tab->getName()));
        }

        //maybe substr here
        $tab->setName(strip_tags($tab->getLongTitle()));

        $this->om->persist($tab);
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
}
