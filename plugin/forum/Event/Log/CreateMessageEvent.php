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
use Claroline\ForumBundle\Entity\Message;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;

class CreateMessageEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-claroline_forum-create_message';

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;

        $details = array(
            'message' => array(
                'id' => $message->getId(),
            ),
            'subject' => array(
                'id' => $message->getSubject()->getId(),
            ),
            'category' => array(
                'id' => $message->getSubject()->getCategory()->getId(),
            ),
            'forum' => array(
                'id' => $message->getSubject()->getCategory()->getForum()->getId(),
            ),
        );

        parent::__construct($message->getSubject()->getCategory()->getForum()->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE, self::DISPLAYED_ADMIN);
    }

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify()
    {
        return true;
    }

    public function getSendToFollowers()
    {
        return true;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        return array();
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return array();
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this::ACTION;
    }
    /**
     * Get iconKey string.
     *
     * @return string
     */
    public function getIconKey()
    {
        return 'forum';
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $details = $this->details;
        $details['forum']['name'] = $this->message->getSubject()->getCategory()->getForum()->getResourceNode()->getName();

        return $details;
    }
}
