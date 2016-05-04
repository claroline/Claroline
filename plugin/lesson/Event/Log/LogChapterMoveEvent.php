<?php

namespace Icap\LessonBundle\Event\Log;

use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Entity\Chapter;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;

class LogChapterMoveEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_lesson-chapter_move';

    /**
     * @param Lesson  $lesson
     * @param Chapter $chapter
     * @param Chapter $oldparent
     * @param Chapter $newparent
     */
    public function __construct(Lesson $lesson, Chapter $chapter, Chapter $oldparent, Chapter $newparent)
    {
        $details = array(
            'chapter' => array(
                'lesson' => $lesson->getId(),
                'chapter' => $chapter->getId(),
                'title' => $chapter->getTitle(),
                'old_parent' => $oldparent->getTitle(),
                'new_parent' => $newparent->getTitle(),
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
