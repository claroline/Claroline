<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Event\Log;

use Claroline\CoreBundle\Entity\AbstractRoleSubject;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Icap\NotificationBundle\Entity\NotifiableInterface;

class LogRoleSubscribeEvent extends LogGenericEvent implements NotifiableInterface
{
    const ACTION_USER = 'workspace-role-subscribe_user';
    const ACTION_GROUP = 'workspace-role-subscribe_group';
    protected $receiver = null;
    protected  $receiverGroup = null;
    protected $role = null;
    protected $details;

    /**
     * Constructor.
     */
    public function __construct(Role $role, AbstractRoleSubject $subject)
    {
        $this->role = $role;
        $details = array('role' => array('name' => $role->getTranslationKey()));

        if ($role->getWorkspace()) {
            $details['workspace'] = array('name' => $role->getWorkspace()->getName());
        }

        if ($subject instanceof User) {
            $details['receiverUser'] = array(
                'firstName' => $subject->getFirstName(),
                'lastName' => $subject->getLastName()
            );
            $action = self::ACTION_USER;
            $this->receiver = $subject;
        } else {
            $details['receiverGroup'] = array(
                'name' => $subject->getName()
            );
            $action = self::ACTION_GROUP;
            $this->receiverGroup = $subject;
        }

        $this->details = $details;
        parent::__construct(
            $action,
            $details,
            $this->receiver,
            $this->receiverGroup,
            null,
            $role,
            $role->getWorkspace()
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(self::DISPLAYED_WORKSPACE);
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return boolean
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
        if ($this->receiver !== null) {
            return array($this->receiver->getId());
        } else {
            return $this->receiverGroup->getUserIds();
        }
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        $userIds = array();
        $currentGroupId = -1;
        //First of all we need to test if subject is group or user
        //In case of group we need to exclude all these users that already exist in role
        if ($this->receiverGroup !== null) {
            $currentGroupId = $this->receiverGroup->getId();
            $roleUsers = $this->role->getUsers();
            foreach ($roleUsers as $user) {
                array_push($userIds, $user->getId());
            }
        }

        //For both cases (user or group) we need to exclude all users already enrolled in other groups
        $roleGroups = $this->role->getGroups();
        foreach ($roleGroups as $group) {
            if ($group->getId() != $currentGroupId) {
                $userIds = array_merge($userIds, $group->getUserIds());
            }
        }
        $userIds = array_unique($userIds);

        return $userIds;
    }

    /**
     * Get actionKey string.
     *
     * @return string
     */
    public function getActionKey()
    {
        if ($this->receiver !== null) {
            return $this::ACTION_USER;
        } else {
            return $this::ACTION_GROUP;
        }
    }

    /**
     * Get iconKey string.
     *
     * @return string
     */
    public function getIconKey()
    {
        //Icon key is null here because we need default icon for platform notifications
        return null;
    }

    /**
     * Get details
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $notificationDetails = array_merge($this->details, array());

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not
     *
     * @return boolean
     */
    public function isAllowedToNotify()
    {
        return true;
    }
}
