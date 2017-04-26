<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use Claroline\BundleRecorder\Log\LoggableTrait;
use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Widget\WidgetHomeTabConfig;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DI\Service("claroline.manager.home_tab_manager")
 */
class HomeTabManager
{
    use LoggableTrait;
    /** @var HomeTabConfigRepository */
    private $homeTabConfigRepo;
    /** @var HomeTabRepository */
    private $homeTabRepo;
    /** @var WidgetHomeTabConfigRepository */
    private $widgetHomeTabConfigRepo;
    private $om;
    private $container;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"        = @DI\Inject("claroline.persistence.object_manager"),
     *     "container" =  @DI\Inject("service_container")
     * })
     */
    public function __construct(ContainerInterface $container, ObjectManager $om)
    {
        $this->homeTabRepo = $om->getRepository('ClarolineCoreBundle:Home\HomeTab');
        $this->homeTabConfigRepo = $om->getRepository('ClarolineCoreBundle:Home\HomeTabConfig');
        $this->widgetHomeTabConfigRepo = $om->getRepository('ClarolineCoreBundle:Widget\WidgetHomeTabConfig');
        $this->container = $container;
        $this->om = $om;
        $this->homeTabRepo = $om->getRepository('ClarolineCoreBundle:Home\HomeTab');
        $this->homeTabConfigRepo = $om->getRepository('ClarolineCoreBundle:Home\HomeTabConfig');
        $this->widgetHomeTabConfigRepo = $om->getRepository('ClarolineCoreBundle:Widget\WidgetHomeTabConfig');
    }

    public function persistHomeTabConfigs(HomeTab $homeTab = null, HomeTabConfig $homeTabConfig = null)
    {
        if (!is_null($homeTab)) {
            $this->om->persist($homeTab);
        }
        if (!is_null($homeTabConfig)) {
            $this->om->persist($homeTabConfig);
        }
        $this->om->flush();
    }

    public function insertHomeTab(HomeTab $homeTab)
    {
        $this->om->persist($homeTab);
        $this->om->flush();
    }

    public function deleteHomeTab(HomeTab $homeTab)
    {
        $this->om->remove($homeTab);
        $this->om->flush();
    }

    public function insertHomeTabConfig(HomeTabConfig $homeTabConfig)
    {
        $this->om->persist($homeTabConfig);
        $this->om->flush();
    }

    public function deleteHomeTabConfig(HomeTabConfig $homeTabConfig)
    {
        $this->om->remove($homeTabConfig);
        $this->om->flush();
    }

    public function updateVisibility(HomeTabConfig $homeTabConfig, $visible)
    {
        $homeTabConfig->setVisible($visible);
        $this->om->flush();
    }

    public function updateLock(HomeTabConfig $homeTabConfig, $locked)
    {
        $homeTabConfig->setLocked($locked);
        $this->om->flush();
    }

    public function importFromCsv($file)
    {
        $data = file_get_contents($file);
        $data = $this->container->get('claroline.utilities.misc')->formatCsvOutput($data);
        $lines = str_getcsv($data, PHP_EOL);
        $this->om->startFlushSuite();
        $i = 0;

        foreach ($lines as $line) {
            $values = str_getcsv($line, ';');
            $code = $values[0];
            $workspace = $this->om->getRepository('ClarolineCoreBundle:Workspace\Workspace')->findOneByCode($code);

            $name = $values[1];
            $tab = $this->om->getRepository('ClarolineCoreBundle:Home\HomeTab')->findBy(['workspace' => $workspace, 'name' => $name]);
            if (!$tab) {
                $this->createHomeTab($name, $workspace);
                ++$i;
            } else {
                $this->log("Tab {$name} already exists for workspace {$code}");
            }

            if ($i % 100 === 0) {
                $this->om->forceFlush();
                $this->om->clear();
            }
        }

        $this->om->endFlushSuite();
    }

    public function createHomeTab($name, Workspace $workspace = null)
    {
        $type = $workspace ? 'workspace' : 'user';
        $homeTab = new HomeTab();
        $homeTab->setName($name);
        $homeTab->setWorkspace($workspace);
        $homeTab->setType($type);

        $homeTabConfig = new HomeTabConfig();
        $homeTabConfig->setHomeTab($homeTab);
        $homeTabConfig->setType('workspace');
        $homeTabConfig->setWorkspace($workspace);

        $tabsInserted = $this->homeTabRepo->findByWorkspace($workspace);
        $tabsToInsert = $this->getTabsScheduledForInsert($workspace);
        $index = count($tabsInserted) + count($tabsToInsert);
        $homeTabConfig->setTabOrder($index);

        $this->om->persist($homeTabConfig);
        $this->om->persist($homeTab);
        $this->om->flush();

        $this->log("Creating HomeTab {$name} for workspace {$workspace->getCode()}.");
    }

    public function getTabsScheduledForInsert(Workspace $workspace)
    {
        $scheduledForInsert = $this->om->getUnitOfWork()->getScheduledEntityInsertions();
        $res = [];

        foreach ($scheduledForInsert as $entity) {
            if (get_class($entity) === 'Claroline\CoreBundle\Entity\Home\HomeTab') {
                if ($entity->getWorkspace()->getCode() === $workspace->getCode()) {
                    $res[] = $entity;
                }
            }
        }

        return $res;
    }

    public function reorderDesktopHomeTabConfigs(User $user, HomeTabConfig $homeTabConfig, $nextHTCId)
    {
        $htcs = $this->homeTabConfigRepo->findDesktopHomeTabConfigsByUser($user);
        $nextId = intval($nextHTCId);
        $order = 1;
        $updated = false;

        foreach ($htcs as $htc) {
            if ($htc === $homeTabConfig) {
                continue;
            } elseif ($htc->getId() === $nextId) {
                $homeTabConfig->setTabOrder($order);
                $updated = true;
                $this->om->persist($homeTabConfig);
                ++$order;
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            } else {
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            }
        }
        if (!$updated) {
            $homeTabConfig->setTabOrder($order);
            $this->om->persist($homeTabConfig);
        }
        $this->om->flush();
    }

    public function reorderWorkspaceHomeTabConfigs(Workspace $workspace, HomeTabConfig $homeTabConfig, $nextHTCId)
    {
        $htcs = $this->homeTabConfigRepo->findWorkspaceHomeTabConfigsByWorkspace($workspace);
        $nextId = intval($nextHTCId);
        $order = 1;
        $updated = false;

        foreach ($htcs as $htc) {
            if ($htc === $homeTabConfig) {
                continue;
            } elseif ($htc->getId() === $nextId) {
                $homeTabConfig->setTabOrder($order);
                $updated = true;
                $this->om->persist($homeTabConfig);
                ++$order;
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            } else {
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            }
        }
        if (!$updated) {
            $homeTabConfig->setTabOrder($order);
            $this->om->persist($homeTabConfig);
        }
        $this->om->flush();
    }

    public function reorderAdminHomeTabConfigs($homeTabType, HomeTabConfig $homeTabConfig, $nextHTCId)
    {
        $htcs = ($homeTabType === 'desktop') ?
            $this->homeTabConfigRepo->findAdminDesktopHomeTabConfigs() :
            $this->homeTabConfigRepo->findAdminWorkspaceHomeTabConfigs();
        $nextId = intval($nextHTCId);
        $order = 1;
        $updated = false;

        foreach ($htcs as $htc) {
            if ($htc === $homeTabConfig) {
                continue;
            } elseif ($htc->getId() === $nextId) {
                $homeTabConfig->setTabOrder($order);
                $updated = true;
                $this->om->persist($homeTabConfig);
                ++$order;
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            } else {
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            }
        }
        if (!$updated) {
            $homeTabConfig->setTabOrder($order);
            $this->om->persist($homeTabConfig);
        }
        $this->om->flush();
    }

    public function reorderHomeTabConfigsByType($type, HomeTabConfig $homeTabConfig, $nextHTCId)
    {
        $htcs = $this->homeTabConfigRepo->findHomeTabConfigsByType($type);
        $nextId = intval($nextHTCId);
        $order = 1;
        $updated = false;

        foreach ($htcs as $htc) {
            if ($htc === $homeTabConfig) {
                continue;
            } elseif ($htc->getId() === $nextId) {
                $homeTabConfig->setTabOrder($order);
                $updated = true;
                $this->om->persist($homeTabConfig);
                ++$order;
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            } else {
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            }
        }
        if (!$updated) {
            $homeTabConfig->setTabOrder($order);
            $this->om->persist($homeTabConfig);
        }
        $this->om->flush();
    }

    public function reorderHomeTabConfigsByUserAndType(User $user, $type, HomeTabConfig $homeTabConfig, $nextHTCId)
    {
        $htcs = $this->homeTabConfigRepo->findHomeTabConfigsByUserAndType($user, $type);
        $nextId = intval($nextHTCId);
        $order = 1;
        $updated = false;

        foreach ($htcs as $htc) {
            if ($htc === $homeTabConfig) {
                continue;
            } elseif ($htc->getId() === $nextId) {
                $homeTabConfig->setTabOrder($order);
                $updated = true;
                $this->om->persist($homeTabConfig);
                ++$order;
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            } else {
                $htc->setTabOrder($order);
                $this->om->persist($htc);
                ++$order;
            }
        }
        if (!$updated) {
            $homeTabConfig->setTabOrder($order);
            $this->om->persist($homeTabConfig);
        }
        $this->om->flush();
    }

    public function createWorkspaceVersion(HomeTabConfig $homeTabConfig, Workspace $workspace)
    {
        $newHomeTabConfig = new HomeTabConfig();
        $newHomeTabConfig->setHomeTab($homeTabConfig->getHomeTab());
        $newHomeTabConfig->setType($homeTabConfig->getType());
        $newHomeTabConfig->setWorkspace($workspace);
        $newHomeTabConfig->setVisible($homeTabConfig->isVisible());
        $newHomeTabConfig->setLocked($homeTabConfig->isLocked());
        $newHomeTabConfig->setTabOrder($homeTabConfig->getTabOrder());
        $this->om->persist($newHomeTabConfig);
        $this->om->flush();

        return $newHomeTabConfig;
    }

    public function createUserVersion(HomeTabConfig $homeTabConfig, User $user)
    {
        $newHomeTabConfig = new HomeTabConfig();
        $newHomeTabConfig->setHomeTab($homeTabConfig->getHomeTab());
        $newHomeTabConfig->setType($homeTabConfig->getType());
        $newHomeTabConfig->setUser($user);
        $newHomeTabConfig->setVisible($homeTabConfig->isVisible());
        $newHomeTabConfig->setLocked($homeTabConfig->isLocked());
        $newHomeTabConfig->setTabOrder($homeTabConfig->getTabOrder());
        $newHomeTabConfig->setDetails($homeTabConfig->getDetails());
        $this->om->persist($newHomeTabConfig);
        $this->om->flush();

        return $newHomeTabConfig;
    }

    public function generateAdminHomeTabConfigsByUser(User $user, array $roleNames = [])
    {
        $adminHTC = [];
        $adminHomeTabConfigs = $this->homeTabConfigRepo
            ->findAdminDesktopHomeTabConfigsByRoles($roleNames);

        foreach ($adminHomeTabConfigs as $adminHomeTabConfig) {
            if ($adminHomeTabConfig->isLocked()) {
                if ($adminHomeTabConfig->isVisible()) {
                    $adminHTC[] = $adminHomeTabConfig;
                }
            } else {
                $existingCustomHTC = $this->homeTabConfigRepo->findOneBy(
                    [
                        'homeTab' => $adminHomeTabConfig->getHomeTab(),
                        'user' => $user,
                    ]
                );

                if (is_null($existingCustomHTC)) {
                    $customHTC = $this->createUserVersion(
                        $adminHomeTabConfig,
                        $user
                    );
                    $adminHTC[] = $customHTC;
                } else {
                    $adminHTC[] = $existingCustomHTC;
                }
            }
        }

        return $adminHTC;
    }

    public function filterVisibleHomeTabConfigs(array $homeTabConfigs)
    {
        $visibleHomeTabConfigs = [];

        foreach ($homeTabConfigs as $homeTabConfig) {
            if ($homeTabConfig->isVisible()) {
                $visibleHomeTabConfigs[] = $homeTabConfig;
            }
        }

        return $visibleHomeTabConfigs;
    }

    public function checkHomeTabLock(HomeTab $homeTab)
    {
        $adminHomeTabConfig = $this->homeTabConfigRepo->findOneBy(
            [
                'homeTab' => $homeTab,
                'type' => 'admin_desktop',
                'user' => null,
                'workspace' => null,
            ]
        );

        return !is_null($adminHomeTabConfig) ?
            $adminHomeTabConfig->isLocked() :
            false;
    }

    public function checkHomeTabVisibilityForConfigByUser(HomeTab $homeTab, User $user)
    {
        $adminHomeTabConfig = $this->homeTabConfigRepo->findOneBy(
            [
                'homeTab' => $homeTab,
                'type' => 'admin_desktop',
                'user' => null,
                'workspace' => null,
            ]
        );
        $userHomeTabConfig = $this->homeTabConfigRepo->findOneBy(
            [
                'homeTab' => $homeTab,
                'user' => $user,
            ]
        );

        if (is_null($adminHomeTabConfig) && is_null($userHomeTabConfig)) {
            $visible = false;
        } elseif (is_null($userHomeTabConfig)) {
            $visible = $adminHomeTabConfig->isVisible();
        } elseif (is_null($adminHomeTabConfig)) {
            $visible = true;
        } else {
            $visible = $adminHomeTabConfig->isLocked() ? $adminHomeTabConfig->isVisible() : true;
        }

        return $visible;
    }

    public function checkHomeTabVisibilityByUser(HomeTab $homeTab, User $user)
    {
        $adminHomeTabConfig = $this->homeTabConfigRepo->findOneBy(
            [
                'homeTab' => $homeTab,
                'type' => 'admin_desktop',
                'user' => null,
                'workspace' => null,
            ]
        );
        $userHomeTabConfig = $this->homeTabConfigRepo->findOneBy(
            [
                'homeTab' => $homeTab,
                'user' => $user,
            ]
        );

        if (is_null($adminHomeTabConfig) && is_null($userHomeTabConfig)) {
            return false;
        } elseif (is_null($userHomeTabConfig)) {
            return $adminHomeTabConfig->isVisible();
        } elseif (is_null($adminHomeTabConfig)) {
            return $userHomeTabConfig->isVisible();
        } else {
            $visible = $adminHomeTabConfig->isLocked() ?
                $adminHomeTabConfig->isVisible() :
                $userHomeTabConfig->isVisible();

            return $visible;
        }
    }

    public function checkHomeTabVisibilityByWorkspace(HomeTab $homeTab, Workspace $workspace)
    {
        $homeTabConfig = $this->homeTabConfigRepo->findOneBy(
            [
                'homeTab' => $homeTab,
                'workspace' => $workspace,
            ]
        );

        if (is_null($homeTabConfig)) {
            return false;
        }

        return $homeTabConfig->isVisible();
    }

    public function checkHomeTabVisibilityByIdAndWorkspace($homeTabId, Workspace $workspace)
    {
        $homeTabConfig = $this->homeTabConfigRepo->checkHomeTabVisibilityByIdAndWorkspace($homeTabId, $workspace);

        if (is_null($homeTabConfig) || count($homeTabConfig) !== 1) {
            return false;
        }

        return true;
    }

    public function insertWidgetHomeTabConfig(WidgetHomeTabConfig $widgetHomeTabConfig)
    {
        $this->om->persist($widgetHomeTabConfig);
        $this->om->flush();
    }

    public function deleteWidgetHomeTabConfig(WidgetHomeTabConfig $widgetHomeTabConfig)
    {
        $this->om->remove($widgetHomeTabConfig);
        $this->om->flush();
    }

    public function changeVisibilityWidgetHomeTabConfig(WidgetHomeTabConfig $widgetHomeTabConfig, $visible = null)
    {
        $isVisible = is_null($visible) ? !$widgetHomeTabConfig->isVisible() : $visible;
        $widgetHomeTabConfig->setVisible($isVisible);
        $this->om->flush();
    }

    public function changeLockWidgetHomeTabConfig(WidgetHomeTabConfig $widgetHomeTabConfig)
    {
        $isLocked = !$widgetHomeTabConfig->isLocked();
        $widgetHomeTabConfig->setLocked($isLocked);
        $this->om->flush();
    }

    public function generateCopyOfAdminWorkspaceHomeTabs(Workspace $workspace)
    {
        $adminHomeTabConfigs = $this->homeTabConfigRepo->findAdminWorkspaceHomeTabConfigs();

        foreach ($adminHomeTabConfigs as $adminHomeTabConfig) {
            // Create HomeTab
            $adminHomeTab = $adminHomeTabConfig->getHomeTab();
            $homeTab = new HomeTab();
            $homeTab->setName($adminHomeTab->getName());
            $homeTab->setType('workspace');
            $homeTab->setWorkspace($workspace);
            $this->om->persist($homeTab);
            $this->om->flush();

            // Create HomeTabConfig
            $homeTabConfig = new HomeTabConfig();
            $homeTabConfig->setHomeTab($homeTab);
            $homeTabConfig->setWorkspace($workspace);
            $homeTabConfig->setType('workspace');
            $homeTabConfig->setVisible($adminHomeTabConfig->isVisible());
            $homeTabConfig->setLocked(false);
            $homeTabConfig->setTabOrder($adminHomeTabConfig->getTabOrder());
            $this->om->persist($homeTabConfig);

            // Create WidgetHomeTabConfig
            $adminWidgetHomeTabConfigs = $this->widgetHomeTabConfigRepo->findAdminWidgetConfigs($adminHomeTab);

            foreach ($adminWidgetHomeTabConfigs as $adminWidgetHomeTabConfig) {
                $widgetHomeTabConfig = new WidgetHomeTabConfig();
                $widgetHomeTabConfig->setHomeTab($homeTab);

                $adminWidgetInstance = $adminWidgetHomeTabConfig->getWidgetInstance();
                $workspaceWidgetInstance = new WidgetInstance();
                $workspaceWidgetInstance->setIsAdmin(false);
                $workspaceWidgetInstance->setIsDesktop(false);
                $workspaceWidgetInstance->setName($adminWidgetInstance->getName());
                $workspaceWidgetInstance->setWidget($adminWidgetInstance->getWidget());
                $workspaceWidgetInstance->setWorkspace($workspace);
                $this->om->persist($workspaceWidgetInstance);

                $widgetHomeTabConfig->setWidgetInstance($workspaceWidgetInstance);
                $widgetHomeTabConfig->setWorkspace($workspace);
                $widgetHomeTabConfig->setType('workspace');
                $widgetHomeTabConfig->setVisible($adminWidgetHomeTabConfig->isVisible());
                $widgetHomeTabConfig->setLocked(false);
                $widgetHomeTabConfig->setWidgetOrder($adminWidgetHomeTabConfig->getWidgetOrder());
                $this->om->persist($widgetHomeTabConfig);
            }

            $this->om->flush();
        }
    }

    /**
     * HomeTabRepository access methods.
     */
    public function getHomeTabById($homeTabId)
    {
        return $this->homeTabRepo->findOneById($homeTabId);
    }

    public function getAdminHomeTabByIdAndType($homeTabId, $homeTabType)
    {
        $criterias = [
            'id' => $homeTabId,
            'user' => null,
            'workspace' => null,
            'type' => 'admin_'.$homeTabType,
        ];

        return $this->homeTabRepo->findOneBy($criterias);
    }

    public function getHomeTabByIdAndWorkspace($homeTabId, Workspace $workspace)
    {
        return $this->homeTabRepo->findOneBy(['id' => $homeTabId, 'workspace' => $workspace]);
    }

    public function getHomeTabByIdAndType($homeTabId, $type)
    {
        return $this->homeTabRepo->findOneBy(['id' => $homeTabId, 'type' => $type]);
    }

    /**
     * HomeTabConfigRepository access methods.
     */
    public function getAdminDesktopHomeTabConfigs()
    {
        return $this->homeTabConfigRepo->findAdminDesktopHomeTabConfigs();
    }

    public function getAdminDesktopHomeTabConfigsByRoles(array $roleNames)
    {
        return $this->homeTabConfigRepo->findAdminDesktopHomeTabConfigsByRoles($roleNames);
    }

    public function getVisibleAdminDesktopHomeTabConfigsByRoles(array $roleNames)
    {
        return $this->homeTabConfigRepo->findVisibleAdminDesktopHomeTabConfigsByRoles($roleNames);
    }

    public function getAdminWorkspaceHomeTabConfigs()
    {
        return $this->homeTabConfigRepo->findAdminWorkspaceHomeTabConfigs();
    }

    public function getAdminDesktopHomeTabConfigByHomeTab(HomeTab $homeTab)
    {
        return $this->homeTabConfigRepo->findAdminDesktopHomeTabConfigByHomeTab($homeTab);
    }

    public function getDesktopHomeTabConfigsByUser(User $user)
    {
        return $this->homeTabConfigRepo->findDesktopHomeTabConfigsByUser($user);
    }

    public function getWorkspaceHomeTabConfigsByWorkspace(Workspace $workspace)
    {
        return $this->homeTabConfigRepo->findWorkspaceHomeTabConfigsByWorkspace($workspace);
    }

    public function getVisibleAdminDesktopHomeTabConfigs()
    {
        return $this->homeTabConfigRepo->findVisibleAdminDesktopHomeTabConfigs();
    }

    public function getVisibleAdminWorkspaceHomeTabConfigs()
    {
        return $this->homeTabConfigRepo->findVisibleAdminWorkspaceHomeTabConfigs();
    }

    public function getVisibleDesktopHomeTabConfigsByUser(User $user)
    {
        return $this->homeTabConfigRepo->findVisibleDesktopHomeTabConfigsByUser($user);
    }

    public function getVisibleWorkspaceHomeTabConfigsByWorkspace(Workspace $workspace)
    {
        return $this->homeTabConfigRepo->findVisibleWorkspaceHomeTabConfigsByWorkspace($workspace);
    }

    public function getVisibleWorkspaceHomeTabConfigsByWorkspaceAndRoles(Workspace $workspace, array $roleNames)
    {
        return $this->homeTabConfigRepo->findVisibleWorkspaceHomeTabConfigsByWorkspaceAndRoles($workspace, $roleNames);
    }

    public function getOrderOfLastDesktopHomeTabConfigByUser(User $user)
    {
        return $this->homeTabConfigRepo->findOrderOfLastDesktopHomeTabByUser($user);
    }

    public function getOrderOfLastWorkspaceHomeTabConfigByWorkspace(Workspace $workspace)
    {
        return $this->homeTabConfigRepo->findOrderOfLastWorkspaceHomeTabByWorkspace($workspace);
    }

    public function getOrderOfLastAdminDesktopHomeTabConfig()
    {
        return $this->homeTabConfigRepo->findOrderOfLastAdminDesktopHomeTab();
    }

    public function getOrderOfLastAdminWorkspaceHomeTabConfig()
    {
        return $this->homeTabConfigRepo->findOrderOfLastAdminWorkspaceHomeTab();
    }

    public function getHomeTabConfigByHomeTabAndWorkspace(HomeTab $homeTab, Workspace $workspace)
    {
        return $this->homeTabConfigRepo->findOneBy(['homeTab' => $homeTab, 'workspace' => $workspace]);
    }

    public function getHomeTabConfigByHomeTabAndUser(HomeTab $homeTab, User $user)
    {
        return $this->homeTabConfigRepo->findOneBy(['homeTab' => $homeTab, 'user' => $user]);
    }

    public function getHomeTabConfigsByWorkspaceAndHomeTabs(Workspace $workspace, array $homeTabs)
    {
        return count($homeTabs) > 0 ?
            $this->homeTabConfigRepo->findHomeTabConfigsByWorkspaceAndHomeTabs($workspace, $homeTabs) :
            [];
    }

    public function getOneVisibleWorkspaceUserHTC(HomeTab $homeTab, User $user)
    {
        return $this->homeTabConfigRepo->findOneVisibleWorkspaceUserHTC($homeTab, $user);
    }

    public function getVisibleWorkspaceUserHTCsByUser(User $user)
    {
        return $this->homeTabConfigRepo->findVisibleWorkspaceUserHTCsByUser($user);
    }

    public function getOrderOfLastWorkspaceUserHomeTabByUser(User $user)
    {
        return $this->homeTabConfigRepo->findOrderOfLastWorkspaceUserHomeTabByUser($user);
    }

    public function getHomeTabConfigsByType($type)
    {
        return $this->homeTabConfigRepo->findHomeTabConfigsByType($type);
    }

    public function getHomeTabConfigsByUserAndType(User $user, $type)
    {
        return $this->homeTabConfigRepo->findHomeTabConfigsByUserAndType($user, $type);
    }

    public function getOrderOfLastHomeTabByType($type)
    {
        return $this->homeTabConfigRepo->findOrderOfLastHomeTabByType($type);
    }

    public function getOrderOfLastHomeTabByUserAndType(User $user, $type)
    {
        return $this->homeTabConfigRepo->findOrderOfLastHomeTabByUserAndType($user, $type);
    }

    /**
     * WidgetHomeTabConfigRepository access methods.
     */
    public function getAdminWidgetConfigs(HomeTab $homeTab)
    {
        return $this->widgetHomeTabConfigRepo->findAdminWidgetConfigs($homeTab);
    }

    public function getVisibleAdminWidgetConfigs(HomeTab $homeTab)
    {
        return $this->widgetHomeTabConfigRepo->findVisibleAdminWidgetConfigs($homeTab);
    }

    public function getWidgetConfigsByUser(HomeTab $homeTab, User $user)
    {
        return $this->widgetHomeTabConfigRepo->findWidgetConfigsByUser($homeTab, $user);
    }

    public function getVisibleWidgetConfigsByUser(HomeTab $homeTab, User $user)
    {
        return $this->widgetHomeTabConfigRepo->findVisibleWidgetConfigsByUser($homeTab, $user);
    }

    public function getWidgetConfigsByWorkspace(HomeTab $homeTab, Workspace $workspace)
    {
        return $this->widgetHomeTabConfigRepo->findWidgetConfigsByWorkspace($homeTab, $workspace);
    }

    public function getVisibleWidgetConfigsByWorkspace(HomeTab $homeTab, Workspace $workspace)
    {
        return $this->widgetHomeTabConfigRepo->findVisibleWidgetConfigsByWorkspace($homeTab, $workspace);
    }

    public function getVisibleWidgetConfigsByTabIdAndWorkspace($homeTabId, Workspace $workspace)
    {
        return $this->widgetHomeTabConfigRepo->findVisibleWidgetConfigsByTabIdAndWorkspace($homeTabId, $workspace);
    }

    public function getVisibleWidgetConfigByWidgetIdAndTabIdAndWorkspace($widgetId, $homeTabId, Workspace $workspace)
    {
        return $this->widgetHomeTabConfigRepo->findVisibleWidgetConfigByWidgetIdAndTabIdAndWorkspace($widgetId, $homeTabId, $workspace);
    }

    public function getUserAdminWidgetHomeTabConfig(HomeTab $homeTab, WidgetInstance $widgetInstance, User $user)
    {
        return $this->widgetHomeTabConfigRepo->findUserAdminWidgetHomeTabConfig($homeTab,  $widgetInstance, $user);
    }

    public function getWidgetHomeTabConfigsByHomeTabAndType(HomeTab $homeTab, $type)
    {
        return $this->widgetHomeTabConfigRepo->findWidgetHomeTabConfigsByHomeTabAndType($homeTab, $type);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
