<?php

namespace Claroline\AnnouncementBundle\Serializer;

use Claroline\AnnouncementBundle\Entity\Announcement;
use Claroline\AnnouncementBundle\Entity\AnnouncementAggregate;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;

class AnnouncementAggregateSerializer
{
    use SerializerTrait;
    const VISIBLE_POSTS_ONLY = 'visiblePostsOnly';

    /** @var AnnouncementSerializer */
    private $announcementSerializer;

    /**
     * AnnouncementAggregateSerializer constructor.
     */
    public function __construct(
        AnnouncementSerializer $announcementSerializer
    ) {
        $this->announcementSerializer = $announcementSerializer;
    }

    public function getName()
    {
        return 'announcement_aggregate';
    }

    public function getClass()
    {
        return AnnouncementAggregate::class;
    }

    /**
     * @return array
     */
    public function serialize(AnnouncementAggregate $announcements, array $options = [])
    {
        $announcePosts = $announcements->getAnnouncements()->toArray();

        if (in_array(static::VISIBLE_POSTS_ONLY, $options)) {
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

    /**
     * @param AnnouncementAggregate $aggregate
     *
     * @return AnnouncementAggregate
     */
    public function deserialize(array $data, AnnouncementAggregate $aggregate = null, array $options = [])
    {
        $aggregate = $aggregate ?: new AnnouncementAggregate();

        if (!in_array(Options::REFRESH_UUID, $options)) {
            $this->sipe('id', 'setUuid', $data, $aggregate);
        } else {
            $aggregate->refreshUuid();
        }

        $existingAnnounces = [];
        foreach ($aggregate->getAnnouncements() as $announce) {
            $existingAnnounces[$announce->getUuid()] = $announce;
        }

        $announceIds = []; // will be used to remove announces which no longer exist
        if (isset($data['posts'])) {
            foreach ($data['posts'] as $post) {
                $announce = $this->announcementSerializer->deserialize($post, $existingAnnounces[$post['id']] ?? null, $options);

                $announce->setAggregate($aggregate);
                $announceIds[] = $announce->getUuid();
            }
        }

        // remove announces which no longer exist
        foreach ($aggregate->getAnnouncements() as $announce) {
            if (!in_array($announce->getUuid(), $announceIds)) {
                // no longer exists
                $announce->setAggregate(null);
            }
        }

        return $aggregate;
    }
}
