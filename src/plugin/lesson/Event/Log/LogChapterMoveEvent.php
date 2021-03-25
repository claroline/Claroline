<?php

namespace Icap\LessonBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Icap\LessonBundle\Entity\Chapter;
use Icap\LessonBundle\Entity\Lesson;

class LogChapterMoveEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_lesson-chapter_move';

    public function __construct(Lesson $lesson, Chapter $chapter, Chapter $oldparent, Chapter $newparent)
    {
        $details = [
            'chapter' => [
                'lesson' => $lesson->getId(),
                'chapter' => $chapter->getId(),
                'title' => $chapter->getTitle(),
                'slug' => $chapter->getSlug(),
                'old_parent' => $oldparent->getTitle(),
                'new_parent' => $newparent->getTitle(),
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
