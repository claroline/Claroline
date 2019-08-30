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
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
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
    /** @var RoleManager */
    private $roleManager;
    /** @var WorkspaceManager */
    private $workspaceManager;

    public function __construct(ContainerInterface $container, $logger = null)
    {
        $this->logger = $logger;
        $this->container = $container;
        $this->om = $container->get('claroline.persistence.object_manager');
        $this->configHandler = $container->get('claroline.config.platform_config_handler');
        $this->translator = $container->get('translator');
        $this->roleManager = $container->get('claroline.manager.role_manager');
        $this->workspaceManager = $container->get('claroline.manager.workspace_manager');
    }

    public function preUpdate()
    {
        $this->renameTool('platform_dashboard', 'dashboard', true);
        $this->renameTool('agenda_', 'agenda');
        $this->renameTool('resource_manager', 'resources');
        $this->renameTool('users', 'community');
        $this->renameTool('user_management', 'community', true);
        $this->renameTool('data_transfer', 'transfer');
        $this->renameTool('data_transfer', 'transfer', true);
    }

    public function postUpdate()
    {
        $this->updatePlatformOptions();

        $this->removeTool('my_contacts');
        $this->removeTool('workspace_management', true);

        // old inwicast plugin tools
        $this->removeTool('inwicast_portal');
        $this->removeTool('inwicast_configuration', true);

        $this->createDefaultAdminHomeTab();
        $this->updateSlugs();
        $this->createDefaultWorkspaceShortcuts();

        $this->log('Build default workspace');
        $this->workspaceManager->getDefaultModel(false, true);

        $this->log('Build default personal workspace');
        $this->workspaceManager->getDefaultModel(true, true);
    }

    private function updatePlatformOptions()
    {
        // configure new header
        $header = $this->configHandler->getParameter('header_menu');
        if (!empty($header)) {
            $this->configHandler->setParameter('header_menu', [
                'search',
                'history',
                'favourites',
            ]);
        }

        // configure new home
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

        // configure default admin tool
        $this->configHandler->setParameter('admin', [
            'default_tool' => 'home',
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

    private function renameTool($oldName, $newName, $admin = false)
    {
        $tool = $this->om->getRepository($admin ? 'ClarolineCoreBundle:Tool\AdminTool' : 'ClarolineCoreBundle:Tool\Tool')->findOneBy(['name' => $oldName]);
        if (!empty($tool)) {
            $tool->setName($newName);
            $this->om->persist($tool);
            $this->om->flush();
        }

        if (!$admin) {
            $conn = $this->container->get('doctrine.dbal.default_connection');
            $sql = "UPDATE claro_ordered_tool SET name = '${newName}' WHERE name = '${oldName}'";

            $this->log($sql);
            $stmt = $conn->prepare($sql);
            $stmt->execute();
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

    private function createDefaultWorkspaceShortcuts()
    {
        $this->log('Generating default shortcuts for workspaces...');

        $managerShortCuts = [
            ['type' => 'tool', 'name' => 'home'],
            ['type' => 'tool', 'name' => 'resources'],
            ['type' => 'tool', 'name' => 'agenda'],
            ['type' => 'tool', 'name' => 'community'],
            ['type' => 'tool', 'name' => 'dashboard'],
            ['type' => 'action', 'name' => 'favourite'],
            ['type' => 'action', 'name' => 'configure'],
            ['type' => 'action', 'name' => 'impersonation'],
        ];
        $collaboratorShortCuts = [
            ['type' => 'tool', 'name' => 'home'],
            ['type' => 'tool', 'name' => 'resources'],
            ['type' => 'tool', 'name' => 'agenda'],
            ['type' => 'action', 'name' => 'favourite'],
        ];
        $shortcutsRepo = $this->om->getRepository(Shortcuts::class);
        $workspaces = $this->om->getRepository(Workspace::class)->findBy([
            'model' => false,
            'personal' => false,
            'archived' => false,
        ]);
        $i = 0;
        $this->om->startFlushSuite();

        foreach ($workspaces as $workspace) {
            if (0 === count($shortcutsRepo->findBy(['workspace' => $workspace]))) {
                $managerRole = $this->roleManager->getManagerRole($workspace);
                $collaboratorRole = $this->roleManager->getCollaboratorRole($workspace);

                if ($managerRole) {
                    $shortcuts = new Shortcuts();
                    $shortcuts->setWorkspace($workspace);
                    $shortcuts->setRole($managerRole);
                    $shortcuts->setData($managerShortCuts);
                    $this->om->persist($shortcuts);
                }
                if ($collaboratorRole) {
                    $shortcuts = new Shortcuts();
                    $shortcuts->setWorkspace($workspace);
                    $shortcuts->setRole($collaboratorRole);
                    $shortcuts->setData($collaboratorShortCuts);
                    $this->om->persist($shortcuts);
                }
            }
            ++$i;

            if (0 === $i % 200) {
                $this->om->forceFlush();
            }
        }
        $this->om->endFlushSuite();

        $this->log('Default shortcuts for workspaces generated');
    }
}
