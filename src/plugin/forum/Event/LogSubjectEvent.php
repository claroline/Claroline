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

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Claroline\ForumBundle\Entity\Subject;

class LogSubjectEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    /**
     * Constructor.
     */
    public function __construct($action, Subject $subject, array $usersToNotify = [])
    {
        $this->usersToNotify = $usersToNotify;
        $this->subject = $subject;
        $node = $subject->getForum()->getResourceNode();
        $this->action = $action;

        $details = ['forum' => [
          'id' => $subject->getForum()->getId(),
          'uuid' => $subject->getForum()->getUuid(),
        ],
        'subject' => [
          'title' => $subject->getTitle(),
          'id' => $subject->getId(),
          'uuid' => $subject->getUuid(),
        ],
        'owner' => [
            'id' => $subject->getCreator()->getId(),
            'uuid' => $subject->getCreator()->getUuid(),
            'lastName' => $subject->getCreator()->getLastName(),
            'firstName' => $subject->getCreator()->getFirstName(),
        ], ];

        parent::__construct($node, $details);
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
        return [$this->subject->getCreator()->getId()];
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        return $this->action;
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
