<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\Home\HomeTabConfig;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.home_tab_manager")
 */
class HomeTabManager
{
    /** @var HomeTabConfigRepository */
    private $homeTabConfigRepo;
    private $om;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->homeTabConfigRepo = $om->getRepository('ClarolineCoreBundle:Home\HomeTabConfig');
        $this->om = $om;
    }

    public function insertHomeTab(HomeTab $homeTab)
    {
        $this->om->persist($homeTab);
        $this->om->flush();
    }

    public function deleteHomeTab(HomeTab $homeTab, $type, $tabOrder)
    {
        switch ($type) {
            case 'admin_desktop':
                $this->homeTabConfigRepo->updateAdminDesktopOrder($tabOrder);
                break;
            case 'admin_workspace':
                $this->homeTabConfigRepo->updateAdminWorkspaceOrder($tabOrder);
                break;
            case 'desktop':
                $this->homeTabConfigRepo->updateDesktopOrder($homeTab->getUser(), $tabOrder);
                break;
            case 'workspace':
                $this->homeTabConfigRepo->updateWorkspaceOrder($homeTab->getWorkspace(), $tabOrder);
                break;
        }
        $this->om->remove($homeTab);
        $this->om->flush();
    }

    public function insertHomeTabConfig(HomeTabConfig $homeTabConfig)
    {
        $this->om->persist($homeTabConfig);
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

    public function getAdminDesktopHomeTabConfigs()
    {
        return $this->homeTabConfigRepo->findAdminDesktopHomeTabConfigs();
    }

    public function getAdminWorkspaceHomeTabConfigs()
    {
        return $this->homeTabConfigRepo->findAdminWorkspaceHomeTabConfigs();
    }

    public function getDesktopHomeTabConfigsByUser(User $user)
    {
        return $this->homeTabConfigRepo->findDesktopHomeTabConfigsByUser($user);
    }

    public function getWorkspaceHomeTabConfigsByWorkspace(AbstractWorkspace $workspace)
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

    public function getVisibleWorkspaceHomeTabConfigsByWorkspace(AbstractWorkspace $workspace)
    {
        return $this->homeTabConfigRepo->findVisibleWorkspaceHomeTabConfigsByWorkspace($workspace);
    }

    public function getOrderOfLastDesktopHomeTabConfigByUser(User $user)
    {
        return $this->homeTabConfigRepo->findOrderOfLastDesktopHomeTabByUser($user);
    }

    public function getOrderOfLastWorkspaceHomeTabConfigByWorkspace(AbstractWorkspace $workspace)
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

    public function getHomeTabConfigByHomeTabAndWorkspace(HomeTab $homeTab, AbstractWorkspace $workspace)
    {
        return $this->homeTabConfigRepo->findOneBy(array('homeTab' => $homeTab, 'workspace' => $workspace));
    }

    public function getHomeTabConfigByHomeTabAndUser(HomeTab $homeTab, User $user)
    {
        return $this->homeTabConfigRepo->findOneBy(array('homeTab' => $homeTab, 'user' => $user));
    }

    public function createWorkspaceVersion(HomeTabConfig $homeTabConfig, AbstractWorkspace $workspace)
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
        $this->om->persist($newHomeTabConfig);
        $this->om->flush();

        return $newHomeTabConfig;
    }

    public function generateAdminHomeTabConfigsByUser(User $user)
    {
        $adminHTC = array();
        $adminHomeTabConfigs = $this->homeTabConfigRepo->findVisibleAdminDesktopHomeTabConfigs();

        foreach ($adminHomeTabConfigs as $adminHomeTabConfig) {

            if ($adminHomeTabConfig->isLocked()) {
                $adminHTC[] = $adminHomeTabConfig;
            }
            else {
                $existingCustomHTC = $this->homeTabConfigRepo->findOneBy(
                    array(
                        'homeTab' => $adminHomeTabConfig->getHomeTab(),
                        'user' => $user
                    )
                );

                if (is_null($existingCustomHTC)) {
                    $customHTC = $this->createUserVersion(
                        $adminHomeTabConfig,
                        $user
                    );
                    $adminHTC[] = $customHTC;
                }
                else {
                    $adminHTC[] = $existingCustomHTC;
                }
            }
        }

        return $adminHTC;
    }

    public function generateAdminHomeTabConfigsByWorkspace(AbstractWorkspace $workspace)
    {
        $adminHTC = array();
        $adminHomeTabConfigs = $this->homeTabConfigRepo->findVisibleAdminWorkspaceHomeTabConfigs();

        foreach ($adminHomeTabConfigs as $adminHomeTabConfig) {

            if ($adminHomeTabConfig->isLocked()) {
                $adminHTC[] = $adminHomeTabConfig;
            }
            else {
                $existingCustomHTC = $this->homeTabConfigRepo->findOneBy(
                    array(
                        'homeTab' => $adminHomeTabConfig->getHomeTab(),
                        'workspace' => $workspace
                    )
                );

                if (is_null($existingCustomHTC)) {
                    $customHTC = $this->createWorkspaceVersion(
                        $adminHomeTabConfig,
                        $workspace
                    );
                    $adminHTC[] = $customHTC;
                }
                else {
                    $adminHTC[] = $existingCustomHTC;
                }
            }
        }

        return $adminHTC;
    }

    public function filterVisibleHomeTabConfigs(array $homeTabConfigs)
    {
        $visibleHomeTabConfigs = array();

        foreach ($homeTabConfigs as $homeTabConfig) {

            if ($homeTabConfig->isVisible()) {
                $visibleHomeTabConfigs[] = $homeTabConfig;
            }
        }

        return $visibleHomeTabConfigs;
    }
}