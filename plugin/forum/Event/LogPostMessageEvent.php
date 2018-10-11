<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Event;

use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Claroline\ForumBundle\Entity\Message;

class LogPostMessageEvent extends LogGenericEvent implements NotifiableInterface
{
    const ACTION = 'forum_message-create';

    /**
     * Constructor.
     */
    public function __construct(Message $message, array $usersToNotify = [])
    {
        $this->usersToNotify = $usersToNotify;
        $this->message = $message;
        $node = $message->getForum()->getResourceNode();

        parent::__construct(
            self::ACTION,
            [
                'resource' => [
                    'name' => $node->getName(),
                    'path' => $node->getPathForCreationLog(),
                    'id' => $node->getId(),
                    'guid' => $node->getGuid(),
                    'resourceType' => $node->getResourceType()->getName(),
                ],
                'forum' => [
                    'id' => $message->getForum()->getId(),
                    'uuid' => $message->getForum()->getUuid(),
                ],
                'subject' => [
                  'title' => $message->getSubject()->getTitle(),
                  'id' => $message->getSubject()->getId(),
                  'uuid' => $message->getSubject()->getUuid(),
                ],
                'owner' => [
                    'id' => $message->getCreator()->getId(),
                    'uuid' => $message->getCreator()->getUuid(),
                    'lastName' => $message->getCreator()->getLastName(),
                    'firstName' => $message->getCreator()->getFirstName(),
                ],
            ],
            null,
            null,
            $node,
            null,
            $node->getWorkspace(),
            $message->getCreator()
        );
    }

    public function setUsersToNotify(array $usersToNotify)
    {
        $this->usersToNotify = $usersToNotify;
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [];
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
     */
    public function getSendToFollowers()
    {
        return false;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        $ids = [];

        foreach ($this->usersToNotify as $user) {
            $ids[] = $user->getId();
        }

        return $ids;
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return [$this->message->getCreator()->getId()];
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
        //Icon key is null here because we need default icon for platform notifications
        return;
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $notificationDetails = array_merge($this->details, []);

        return $notificationDetails;
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
}
