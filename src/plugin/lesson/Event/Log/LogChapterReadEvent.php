<?php

namespace Icap\LessonBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;

class LogChapterReadEvent extends AbstractLogResourceEvent implements LogNotRepeatableInterface
{
    const ACTION = 'resource-icap_lesson-chapter_read';

    public function __construct(Lesson $lesson, Chapter $chapter)
    {
        $details = [
            'chapter' => [
                'lesson' => $lesson->getId(),
                'chapter' => $chapter->getId(),
                'title' => $chapter->getTitle(),
                'slug' => $chapter->getSlug(),
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

    public function getLogSignature()
    {
        return self::ACTION.'_'.$this->resource->getId();
    }
}
