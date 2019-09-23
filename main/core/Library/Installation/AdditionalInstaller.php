<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation;

use Claroline\CoreBundle\Entity\DataSource;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\Widget;
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Entity\Widget\WidgetContainerConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Widget\WidgetInstanceConfig;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class AdditionalInstaller extends BaseInstaller implements ContainerAwareInterface
{
    public function preInstall()
    {
        $this->setLocale();
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
        $dataWebDir = $this->container->getParameter('claroline.param.data_web_dir');
        $fileSystem = $this->container->get('filesystem');
        $publicFilesDir = $this->container->getParameter('claroline.param.public_files_directory');

        if (!$fileSystem->exists($dataWebDir)) {
            $this->log('Creating symlink to public directory of files directory in web directory...');
            $fileSystem->symlink($publicFilesDir, $dataWebDir);
        } else {
            if (!is_link($dataWebDir)) {
                //we could remove it manually but it might be risky
                $this->log('Symlink from web/data to files/data could not be created, please remove your web/data folder manually', LogLevel::ERROR);
            } else {
                $this->log('Web folder symlinks validated...');
            }
        }

        try {
            $updater = new Updater\Updater110000($this->container);
            $updater->lnPictureDirectory();
            $updater->lnPackageDirectory();
        } catch (\Exception $e) {
            $this->log($e->getMessage(), LogLevel::ERROR);
        }

        $this->setLocale();

        if (version_compare($currentVersion, '12.0.0', '<')) {
            $updater = new Updater\Updater120000($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }

        if (version_compare($currentVersion, '12.5.0', '<')) {
            $updater = new Updater\Updater120500($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {
        $this->setLocale();

        if (version_compare($currentVersion, '6.3.0', '<')) {
            $updater = new Updater\Updater060300($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '6.4.0', '<')) {
            $updater = new Updater\Updater060400($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '6.5.0', '<')) {
            $updater = new Updater\Updater060500($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '6.6.7', '<')) {
            $updater = new Updater\Updater060607($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '6.7.0', '<')) {
            $updater = new Updater\Updater060700($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '6.7.4', '<=')) {
            $updater = new Updater\Updater060704($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '6.8.0', '<')) {
            $updater = new Updater\Updater060800($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '7.0.0', '<')) {
            $updater = new Updater\Updater070000($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '8.0.0', '<')) {
            $updater = new Updater\Updater080000($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '9.1.0', '<')) {
            $updater = new Updater\Updater090100($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '9.2.0', '<')) {
            $updater = new Updater\Updater090200($this->container);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '9.3.0', '<')) {
            $updater = new Updater\Updater090300($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '10.0.0', '<')) {
            $updater = new Updater\Updater100000($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '10.0.30', '<')) {
            $updater = new Updater\Updater100030($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '10.2.0', '<')) {
            $updater = new Updater\Updater100200($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '11.0.0', '<')) {
            $updater = new Updater\Updater110000($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '11.2.0', '<')) {
            $updater = new Updater\Updater110200($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '11.3.0', '<')) {
            $updater = new Updater\Updater110300($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '12.0.0', '<')) {
            $updater = new Updater\Updater120000($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '12.0.15', '<')) {
            $updater = new Updater\Updater120015($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '12.0.21', '<')) {
            $updater = new Updater\Updater120020($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.1.0', '<')) {
            $updater = new Updater\Updater120100($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.1.18', '<')) {
            $updater = new Updater\Updater120118($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }
        if (version_compare($currentVersion, '12.1.23', '<')) {
            $updater = new Updater\Updater120118($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->saveConfigAsJson();
        }
        if (version_compare($currentVersion, '12.2.0', '<')) {
            $updater = new Updater\Updater120200($this->container, $this->logger);
            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.2.12', '<')) {
            $updater = new Updater\Updater120212($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.3.0', '<')) {
            $updater = new Updater\Updater120300($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.3.4', '<')) {
            $updater = new Updater\Updater120304($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.4.12', '<')) {
            $updater = new Updater\Updater120412($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.4.14', '<')) {
            $updater = new Updater\Updater120414($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.5.0', '<')) {
            $updater = new Updater\Updater120500($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.5.1', '<')) {
            $updater = new Updater\Updater120501($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.5.2', '<')) {
            $updater = new Updater\Updater120502($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.5.3', '<')) {
            $updater = new Updater\Updater120503($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.5.4', '<')) {
            $updater = new Updater\Updater120504($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        if (version_compare($currentVersion, '12.5.5', '<')) {
            $updater = new Updater\Updater120505($this->container, $this->logger);

            $updater->setLogger($this->logger);
            $updater->postUpdate();
        }

        $termsOfServiceManager = $this->container->get('claroline.common.terms_of_service_manager');
        $termsOfServiceManager->sendDatas();
    }

    public function end($currentVersion, $targetVersion)
    {
        if ($currentVersion && $targetVersion) {
            if (version_compare($currentVersion, '12.0.0', '<')) {
                $updater = new Updater\Updater120000($this->container, $this->logger);
                $updater->setLogger($this->logger);
                $updater->end();
            }
        }

        $this->container->get('claroline.installation.refresher')->installAssets();
        $this->log('Updating resource icons...');

        try {
            $this->container->get('claroline.manager.icon_set_manager')->setLogger($this->logger);
            $this->container->get('claroline.manager.icon_set_manager')->addDefaultIconSets();
        } catch (\Exception $e) {
            $this->log('Failed to update icons..');
        }

        $om = $this->container->get('claroline.persistence.object_manager');
        $workspaceManager = $this->container->get('claroline.manager.workspace_manager');
        $workspaceManager->setLogger($this->logger);
        $this->log('Update Roles Admin');
        $this->updateRolesAdmin();

        if (!$om->getRepository(Workspace::class)->findOneBy(['code' => 'default_workspace', 'personal' => false, 'model' => true])) {
            $this->log('Build default workspace');
            $workspaceManager->getDefaultModel(false, true);
        }

        if (!$om->getRepository(Workspace::class)->findOneBy(['code' => 'default_personal', 'personal' => true, 'model' => true])) {
            $this->log('Build default personal workspace');
            $workspaceManager->getDefaultModel(true, true);
        }
    }

    private function setLocale()
    {
        $ch = $this->container->get('claroline.config.platform_config_handler');
        $locale = $ch->getParameter('locale_language');
        $translator = $this->container->get('translator');
        $translator->setLocale($locale);
    }

    private function updateRolesAdmin()
    {
        $om = $this->container->get('claroline.persistence.object_manager');

        /** @var Role $role */
        $adminOrganization = $om->getRepository('ClarolineCoreBundle:Role')->findOneByName('ROLE_ADMIN_ORGANIZATION');

        if (!$adminOrganization) {
            $adminOrganization = $this->container->get('claroline.manager.role_manager')->createBaseRole('ROLE_ADMIN_ORGANIZATION', 'admin_organization');
        }

        /** @var AdminTool $tool */
        $userManagement = $om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findOneByName('community');
        $userManagement->addRole($adminOrganization);

        $om->persist($userManagement);
        $om->flush();
    }

    public function postInstall()
    {
        $this->buildDefaultHomeTab();
    }

    private function buildDefaultHomeTab()
    {
        $this->log('Build default home tab');

        $manager = $this->container->get('claroline.persistence.object_manager');
        $translator = $this->container->get('translator');
        $infoName = $translator->trans('informations', [], 'platform');

        $desktopHomeTab = new HomeTab();
        $desktopHomeTab->setType(HomeTab::TYPE_ADMIN_DESKTOP);
        $manager->persist($desktopHomeTab);

        $desktopHomeTabConfig = new HomeTabConfig();
        $desktopHomeTabConfig->setHomeTab($desktopHomeTab);
        $desktopHomeTabConfig->setVisible(true);
        $desktopHomeTabConfig->setLocked(true);
        $desktopHomeTabConfig->setTabOrder(1);
        $desktopHomeTabConfig->setName($infoName);
        $desktopHomeTabConfig->setLongTitle($infoName);
        $manager->persist($desktopHomeTabConfig);

        $translator = $this->container->get('translator');
        $infoName = $translator->trans('my_workspaces', [], 'platform');

        $dataSource = $manager->getRepository(DataSource::class)->findOneByName('my_workspaces');
        $widget = $manager->getRepository(Widget::class)->findOneByName('list');

        $container = new WidgetContainer();
        $container->setHomeTab($desktopHomeTab);
        $manager->persist($container);

        $containerConfig = new WidgetContainerConfig();
        $containerConfig->setLayout([1]);
        $containerConfig->setName($infoName);
        $containerConfig->setWidgetContainer($container);
        $manager->persist($containerConfig);

        $widgetInstance = new WidgetInstance();
        $widgetInstance->setDataSource($dataSource);
        $widgetInstance->setWidget($widget);
        $widgetInstance->setContainer($container);
        $manager->persist($widgetInstance);

        $widgetInstanceConfig = new WidgetInstanceConfig();
        $widgetInstanceConfig->setWidgetInstance($widgetInstance);
        $widgetInstanceConfig->setType('list');
        $manager->persist($widgetInstanceConfig);

        $manager->flush();
    }
}
