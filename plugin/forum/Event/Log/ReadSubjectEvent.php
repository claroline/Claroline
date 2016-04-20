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

class ReadSubjectEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_forum-read_subject';

    /**
     * @param Subject $subject
     */
    public function __construct(Subject $subject)
    {
        $details = array(
            'subject' => array(
                'id' => $subject->getId(),
                'title' => $subject->getTitle(),
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
