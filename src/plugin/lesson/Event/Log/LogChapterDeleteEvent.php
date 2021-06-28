<?php

namespace Icap\LessonBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\LessonBundle\Entity\Lesson;

class LogChapterDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_lesson-chapter_delete';

    /**
     * @param string $chapterName
     */
    public function __construct(Lesson $lesson, $chapterName)
    {
        $details = [
            'chapter' => [
                'lesson' => $lesson->getId(),
                'title' => $chapterName,
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
}
