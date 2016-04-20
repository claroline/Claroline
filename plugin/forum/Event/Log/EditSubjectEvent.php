<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\ForumBundle\Entity\Subject;

class EditSubjectEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_forum-edit_subject';

    /**
     * @param \Claroline\ForumBundle\Entity\Subject $subject
     * @param string                                $oldTitle
     * @param string                                $newTitle
     */
    public function __construct(Subject $subject, $oldTitle, $newTitle)
    {
        $details = array(
            'subject' => array(
                'id' => $subject->getId(),
                'old_title' => $oldTitle,
                'new_title' => $newTitle,
            ),
            'category' => array(
                'id' => $subject->getCategory()->getId(),
            ),
            'forum' => array(
                'id' => $subject->getCategory()->getForum()->getId(),
            ),
        );

        parent::__construct($subject->getCategory()->getForum()->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN);
    }
}
