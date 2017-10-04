<?php

namespace Claroline\AnnouncementBundle\Event\Log;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;

class LogAnnouncementDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_announcement_aggregate-delete';

    /**
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     */
    public function __construct(
        AnnouncementAggregate $aggregate,
        Announcement $announcement
    ) {
        $details = [
            'announcement' => [
                'aggregate' => $aggregate->getId(),
                'title' => $announcement->getTitle(),
                'announcer' => $announcement->getAnnouncer(),
            ],
        ];

        parent::__construct($aggregate->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN];
    }
}
