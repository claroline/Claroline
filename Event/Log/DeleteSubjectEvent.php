<?php

namespace Claroline\ForumBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\ForumBundle\Entity\Subject;

class DeleteSubjectEvent extends AbstractLogResourceEvent
{
    const ACTION = 'forum-delete-subject';

    /**
     * @param Subject $subject
     */
    public function __construct(Subject $subject)
    {
        $details = array(
            'subject' => array(
                'subject' => $subject->getId(),
                'title' => $subject->getTitle()
            ),
            'forum' => array(
                'forum' => $subject->getForum()->getId()
            )
        );

        parent::__construct($subject->getForum()->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN);
    }
}
