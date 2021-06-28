<?php

namespace Icap\LessonBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;

class LogChapterUpdateEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-icap_lesson-chapter_update';

    protected $lesson;

    /**
     * @param array $changeSet
     */
    public function __construct(Lesson $lesson, Chapter $chapter, $changeSet)
    {
        $this->lesson = $lesson;

        $details = [
            'chapter' => [
                'lesson' => $lesson->getId(),
                'chapter' => $chapter->getId(),
                'title' => $chapter->getTitle(),
                'slug' => $chapter->getSlug(),
                'changeSet' => $changeSet,
            ],
        ];

        parent::__construct($lesson->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
     */
    public function getSendToFollowers()
    {
        return true;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        return [];
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return [];
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this::ACTION;
    }

    /**
     * Get iconTypeUrl string.
     *
     * @return string
     */
    public function getIconKey()
    {
        return 'lesson';
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $notificationDetails = array_merge($this->details, []);
        $notificationDetails['resource'] = [
            'id' => $this->lesson->getId(),
            'name' => $this->resource->getName(),
            'type' => $this->resource->getResourceType()->getName(),
        ];

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify()
    {
        return true;
    }
}
