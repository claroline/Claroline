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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Entity\Tab\HomeTabConfig;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\InstallationBundle\Updater\Updater;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class Updater120500 extends Updater
{
    protected $logger;
    private $container;
    /** @var ObjectManager */
    private $om;
    /** @var PlatformConfigurationHandler */
    private $configHandler;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->configHandler = $container->get('claroline.config.platform_config_handler');
        $this->translator = $container->get('translator');
    }

    public function postUpdate()
    {
        $this->updatePlatformOptions();

        $this->removeTool('my_contacts');
        $this->removeTool('workspace_management');
        $this->createDefaultAdminHomeTab();
        $this->updateSlugs();
    }

    private function updatePlatformOptions()
    {
        $header = $this->configHandler->getParameter('header_menu');
        if (!empty($header)) {
            $this->configHandler->setParameter('header_menu', [
                'search',
                'history',
                'favourites',
            ]);
        }

        $homeType = $this->configHandler->getParameter('home.redirection_type');
        $homeData = null;
        if ('login' === $homeType) {
            $homeType = 'none';
        } elseif ('new' === $homeType) {
            $homeType = 'tool';
        } elseif ('url' === $homeType) {
            $homeUrl = $this->configHandler->getParameter('home.redirection_url');
            if (!empty($homeUrl)) {
                $homeData = $homeUrl;
            } else {
                $homeType = 'none';
            }
        }

        $this->configHandler->setParameter('home', [
            'type' => $homeType,
            'data' => $homeData,
        ]);
    }

    private function removeTool($toolName, $admin = false)
    {
        $this->log(sprintf('Removing `%s` tool...', $toolName));

        $tool = $this->om->getRepository($admin ? 'ClarolineCoreBundle:Tool\AdminTool' : 'ClarolineCoreBundle:Tool\Tool')->findOneBy(['name' => $toolName]);
        if (!empty($tool)) {
            $this->om->remove($tool);
            $this->om->flush();
        }
    }

    private function createDefaultAdminHomeTab()
    {
        $tabs = $this->om->getRepository(HomeTab::class)->findBy(['type' => HomeTab::TYPE_ADMIN]);

        if (0 === count($tabs)) {
            $this->log('Creating default admin home tab...');

            $homeTab = new HomeTab();
            $homeTab->setType(HomeTab::TYPE_ADMIN);
            $this->om->persist($homeTab);

            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setLocked(true);
            $homeTabConfig->setTabOrder(1);
            $name = $this->translator->trans('informations', [], 'platform');
            $homeTabConfig->setName($name);
            $homeTabConfig->setLongTitle($name);
            $this->om->persist($homeTabConfig);
            $this->om->flush();

            $this->log('Default admin home tab created.');
        }
    }

    private function updateSlugs()
    {
        $this->log('Generating slugs for workspaces without slugs...');
        $conn = $this->container->get('doctrine.dbal.default_connection');
        $sql = "
             UPDATE claro_workspace workspace set slug = CONCAT(SUBSTR(workspace.code,1,100) , '-', workspace.id) WHERE workspace.slug = NULL
        ";

        $this->log($sql);
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $this->log('Generating slugs for resources without slugs...');
        $conn = $this->container->get('doctrine.dbal.default_connection');
        $sql = "
             UPDATE claro_resource_node node set slug = CONCAT(SUBSTR(node.name,1,100) , '-', node.id) WHERE node.slug = NULL
        ";

        $this->log($sql);
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }
}
