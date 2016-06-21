<?php

namespace Claroline\AnnouncementBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;

class LogAnnouncementCreateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_announcement_aggregate-create';

    /**
     * @param AnnouncementAggregate $aggregate
     * @param Announcement          $announcement
     */
    public function __construct(
        AnnouncementAggregate $aggregate,
        Announcement $announcement
    ) {
        $details = array(
            'announcement' => array(
                'aggregate' => $aggregate->getId(),
                'title' => $announcement->getTitle(),
                'announcer' => $announcement->getAnnouncer(),
            ),
        );

        parent::__construct($aggregate->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN);
    }
}
