<?php

namespace Icap\LessonBundle\Event\Log;

use Icap\LessonBundle\Entity\Lesson;
use Icap\LessonBundle\Entity\Chapter;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;

class LogChapterUpdateEvent extends AbstractLogResourceEvent{

    const ACTION = 'resource-icap_lesson-chapter_update';

    /**
     * @param Lesson $lesson
     * @param Chapter $chapter
     * @param array $changeSet
     */
    public function __construct(Lesson $lesson, Chapter $chapter, $changeSet)
    {
        $details = array(
            'chapter' => array(
                'lesson'    => $lesson->getId(),
                'chapter'    => $chapter->getId(),
                'title'     => $chapter->getTitle(),
                'changeSet' => $changeSet
            )
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