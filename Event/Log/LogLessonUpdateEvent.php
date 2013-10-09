<?php

namespace Icap\LessonBundle\Event\Log;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;

class LogLessonUpdateEvent extends AbstractLogResourceEvent{

    const ACTION = 'resource-icap_lesson-lesson_update';

    /**
     * @param Lesson $lesson
     * @param array $changeSet
     */
    public function __construct(Lesson $lesson, $changeSet)
    {
        $details = array(
            'lesson' => array(
                'lesson'      => $lesson->getId(),
                'title'     => $lesson->getTitle(),
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