<?php

namespace Claroline\AnnouncementBundle\API\Serializer;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\CoreBundle\Security\PermissionCheckerTrait;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.announcement_aggregate")
 * @DI\Tag("claroline.serializer")
 */
class AnnouncementAggregateSerializer
{
    use PermissionCheckerTrait;

    /** @var AnnouncementSerializer */
    private $announcementSerializer;

    /**
     * AnnouncementAggregateSerializer constructor.
     *
     * @DI\InjectParams({
     *     "announcementSerializer" = @DI\Inject("claroline.serializer.announcement")
     * })
     *
     * @param AnnouncementSerializer $announcementSerializer
     */
    public function __construct(
        AnnouncementSerializer $announcementSerializer
    ) {
        $this->announcementSerializer = $announcementSerializer;
    }

    /**
     * @param AnnouncementAggregate $announcements
     *
     * @return array
     */
    public function serialize(AnnouncementAggregate $announcements)
    {
        $announcePosts = $announcements->getAnnouncements()->toArray();
        if (!$this->checkPermission('EDIT', $announcements->getResourceNode())) {
            // filter embed announces to only get visible ones
            $now = new \DateTime('now');
            $announcePosts = array_values(// reindex array for correct serialization
                array_filter($announcePosts, function (Announcement $announcement) use ($now) {
                    return $announcement->isVisible()
                        && (empty($announcement->getVisibleFrom()) || $announcement->getVisibleFrom() <= $now)
                        && (empty($announcement->getVisibleUntil()) || $announcement->getVisibleUntil() > $now);
                })
            );
        }

        return [
            'id' => $announcements->getUuid(),
            'posts' => array_map(function (Announcement $announcement) {
                return $this->announcementSerializer->serialize($announcement);
            }, $announcePosts),
        ];
    }
}
