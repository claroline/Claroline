<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\AnnouncementBundle\Manager;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AnnouncementBundle\Repository\AnnouncementRepository;
use Claroline\CoreBundle\Entity\Workspace\AbstractWorkspace;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\UserManager;
use Claroline\CoreBundle\Manager\MailManager;
use Claroline\CoreBundle\Manager\MessageManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.announcement.manager.announcement_manager")
 */
class AnnouncementManager
{
    /** @var AnnouncementRepository */
    private $announcementRepo;
    private $om;
    private $userManager;
    private $mailManager;
    private $messageManager;
    private $sc;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "userManager"    = @DI\Inject("claroline.manager.user_manager"),
     *     "mailManager"    = @DI\Inject("claroline.manager.mail_manager"),
     *     "messageManager" = @DI\Inject("claroline.manager.message_manager"),
     *     "sc"             = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        ObjectManager $om,
        UserManager $userManager,
        MessageManager $messageManager,
        MailManager $mailManager,
        $sc
    )
    {
        $this->announcementRepo = $om->getRepository('ClarolineAnnouncementBundle:Announcement');
        $this->om               = $om;
        $this->userManager      = $userManager;
        $this->messageManager   = $messageManager;
        $this->mailManager      = $mailManager;
        $this->sc               = $sc;
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
        if (in_array('ROLE_ADMIN', $roles)
            || in_array("ROLE_WS_MANAGER_{$workspace->getGuid()}", $roles)) {
            return $this->announcementRepo->findVisibleByWorkspace($workspace);
        }

        return $this->announcementRepo->findVisibleByWorkspaceAndRoles($workspace, $roles);
    }

    public function getVisibleAnnouncementsByWorkspaces(array $workspaces, array $roles)
    {
        $managerWorkspaces = array();
        $nonManagerWorkspaces = array();

        foreach ($workspaces as $workspace) {
            if (in_array("ROLE_WS_MANAGER_{$workspace->getGuid()}", $roles)) {
                $managerWorkspaces[] = $workspace;
            } else {
                $nonManagerWorkspaces[] = $workspace;
            }
        }

        return $this->announcementRepo->findVisibleByWorkspacesAndRoles(
            $nonManagerWorkspaces,
            $managerWorkspaces,
            $roles
        );
    }

    public function getAllAnnouncementsByAggregate(AnnouncementAggregate $aggregate)
    {
        return $this->announcementRepo->findAllAnnouncementsByAggregate($aggregate);
    }

    public function getVisibleAnnouncementsByAggregate(AnnouncementAggregate $aggregate)
    {
        return $this->announcementRepo->findVisibleAnnouncementsByAggregate($aggregate);
    }

    public function sendMessage(Announcement $announcement)
    {
        $targets = $this->getUsersByResource($announcement->getAggregate()->getResourceNode(), 1);
        $message = $this->messageManager->create(
            $announcement->getContent(),
            $announcement->getTitle(),
            $targets,
            $announcement->getCreator()
        );
        $this->messageManager->send($message);
    }

    public function sendMail(Announcement $announcement)
    {
        $targets = $this->getUsersByResource($announcement->getAggregate()->getResourceNode(), 1);
        $this->mailManager->send(
            $announcement->getTitle(),
            $announcement->getContent(),
            $targets,
            $announcement->getCreator()
        );
    }

    //@todo make a dql request to retrieve the users (it may be a difficult one to do)
    public function getUsersByResource(ResourceNode $node, $mask)
    {
        $rights = $node->getRights();
        $roles = [];

        foreach ($rights as $right) {
            //1 is the default "open" mask
            if ($right->getMask() & 1) {
                $roles[] = $right->getRole();
            }
        }

        return $this->userManager->getByRolesIncludingGroups($roles);
    }
}
