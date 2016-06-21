<?php

namespace Icap\LessonBundle\Event\Log;

use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Entity\Chapter;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;

class LogChapterDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_lesson-chapter_delete';

    /**
     * @param Lesson $lesson
     * @param string $chaptername
     */
    public function __construct(Lesson $lesson, $chaptername)
    {
        $details = array(
            'chapter' => array(
                'lesson' => $lesson->getId(),
                'title' => $chaptername,
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
}
