<?php

namespace Claroline\ForumBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\ForumBundle\Entity\Message;

class EditMessageEvent extends AbstractLogResourceEvent
{
    const ACTION = 'forum-edit-message';

    public function __construct(Message $message, $oldContent, $newContent)
    {
        $details = array(
            'message' => array(
                'message' => $message->getId(),
                'old_content' => $oldContent,
                'new_content' => $newContent
            ),
            'subject' => array(
                'subject' => $message->getSubject()->getId()
            ),
            'forum' => array(
                'forum' => $message->getSubject()->getForum()->getId()
            )
        );

        parent::__construct($message->getSubject()->getForum()->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN);
    }
}
