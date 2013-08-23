<?php

namespace Claroline\CoreBundle\Manager;

use Claroline\CoreBundle\Entity\Home\HomeTab;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.home_tab_manager")
 */
class HomeTabManager
{
    /** @var HomeTabRepository */
    private $homeTabRepo;
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
        $this->homeTabRepo = $om->getRepository('ClarolineCoreBundle:Home\HomeTab');
        $this->om = $om;
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

    public function getWorkspaceHomeTabsByWorkspace(AbstractWorkspace $workspace)
    {
        return $this->homeTabRepo->findWorkspaceHomeTabsByWorkspace($workspace);
    }

    public function getAdminDesktopHomeTabs()
    {
        return $this->homeTabRepo->findAdminDesktopHomeTabs();
    }

    public function getAdminWorkspaceHomeTabs()
    {
        return $this->homeTabRepo->findAdminWorkspaceHomeTabs();
    }

    public function getDesktopHomeTabsByUser(User $user)
    {
        return $this->homeTabRepo->findDesktopHomeTabsByUser($user);
    }

    public function getOrderOfLastDesktopHomeTabByUser(User $user)
    {
        return $this->homeTabRepo->findOrderOfLastDesktopHomeTabByUser($user);
    }

    public function getOrderOfLastWorkspaceHomeTabByWorkspace(AbstractWorkspace $workspace)
    {
        return $this->homeTabRepo->findOrderOfLastWorkspaceHomeTabByWorkspace($workspace);
    }

    public function getOrderOfLastAdminDesktopHomeTab()
    {
        return $this->homeTabRepo->findOrderOfLastAdminDesktopHomeTab();
    }

    public function getOrderOfLastAdminWorkspaceHomeTab()
    {
        return $this->homeTabRepo->findOrderOfLastAdminWorkspaceHomeTab();
    }
}