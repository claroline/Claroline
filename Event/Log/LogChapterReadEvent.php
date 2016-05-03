<?php

namespace Icap\LessonBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\LogNotRepeatableInterface;
use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Entity\Chapter;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;

class LogChapterReadEvent extends AbstractLogResourceEvent implements LogNotRepeatableInterface
{
    const ACTION = 'resource-icap_lesson-chapter_read';

    /**
     * @param Lesson  $lesson
     * @param Chapter $chapter
     */
    public function __construct(Lesson $lesson, Chapter $chapter)
    {
        $details = array(
            'chapter' => array(
                'lesson' => $lesson->getId(),
                'chapter' => $chapter->getId(),
                'title' => $chapter->getTitle(),
            ),
        );
        parent::__construct($lesson->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }

    public function getLogSignature()
    {
        return self::ACTION.'_'.$this->resource->getId();
    }
}
