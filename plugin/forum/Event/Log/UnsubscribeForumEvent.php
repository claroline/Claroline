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
use Claroline\ForumBundle\Entity\Forum;

class UnsubscribeForumEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_forum-unsubscribe';

    /**
     * @param Forum $forum
     */
    public function __construct(Forum $forum)
    {
        $details = array(
            'forum' => array(
                'forum' => $forum->getId(),
            ),
        );

        parent::__construct($forum->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN);
    }
}
