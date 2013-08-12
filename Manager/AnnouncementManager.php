<?php

namespace Claroline\AnnouncementBundle\Manager;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Repository\AnnouncementRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.announcement.manager.announcement_manager")
 */
class AnnouncementManager
{
    /** @var AnnouncementRepository */
    private $announcementRepo;
    private $om;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->announcementRepo = $om->getRepository('ClarolineAnnouncementBundle:Announcement');
        $this->om = $om;
    }

    public function insertAnnouncement(Announcement $announcement)
    {
        $this->om->persist($announcement);
        $this->om->flush();
    }

    public function deleteAnnouncement(Announcement $announcement)
    {
        $this->om->remove($announcement);
        $this->om->flush();
    }

    public function getVisibleAnnouncementsByWorkspace(AbstractWorkspace $workspace, array $roles)
    {
        return $this->announcementRepo->findVisibleAnnouncementsByWorkspace($workspace, $roles);
    }

    public function getVisibleAnnouncementsByWorkspaces(array $workspaces, array $roles)
    {
        return $this->announcementRepo->findVisibleAnnouncementsByWorkspaces($workspaces, $roles);
    }

    public function getAllAnnouncementsByAggregate(AnnouncementAggregate $aggregate)
    {
        return $this->announcementRepo->findAllAnnouncementsByAggregate($aggregate);
    }

    public function getVisibleAnnouncementsByAggregate(AnnouncementAggregate $aggregate)
    {
        return $this->announcementRepo->findVisibleAnnouncementsByAggregate($aggregate);
    }
}