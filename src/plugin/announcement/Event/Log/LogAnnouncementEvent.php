<?php

namespace Claroline\AnnouncementBundle\Event\Log;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;

class LogAnnouncementEvent extends LogGenericEvent
{
    /**
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     */
    public function __construct(
        Announcement $announcement,
        $action
    ) {
        $aggregate = $announcement->getAggregate();
        $node = $aggregate->getResourceNode();

        $details = [
            'announcement' => [
                'aggregate' => $aggregate->getId(),
                'title' => $announcement->getTitle(),
                'announcer' => $announcement->getAnnouncer(),
            ],
        ];

        parent::__construct($action, $details, null, null, $node, null, $node->getWorkspace(), $announcement->getCreator());
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN];
    }
}
