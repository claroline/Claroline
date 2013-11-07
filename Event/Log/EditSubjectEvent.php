<?php

namespace Claroline\ForumBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\ForumBundle\Entity\Subject;

class EditSubjectEvent extends AbstractLogResourceEvent
{
    const ACTION = 'forum-edit-subject';

    public function __construct(Subject $subject, $oldTitle, $newTitle)
    {
        $details = array(
            'subject' => array(
                'subject' => $subject->getId(),
                'old_title' => $oldTitle,
                'new_title' => $newTitle
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
