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

use Claroline\AppBundle\Event\MandatoryEventInterface;

class LogWorkspaceRoleChangeRightEvent extends LogGenericEvent implements MandatoryEventInterface, NotifiableInterface
{
    const ACTION = 'workspace-role-change_right';
    protected $role;
    protected $changeSet;
    protected $details;

    /**
     * Constructor.
     * ChangeSet expected variable is array which contain all modified properties, in the following form:
     * (
     *      'propertyName1' => ['property old value 1', 'property new value 1'],
     *      'propertyName2' => ['property old value 2', 'property new value 2'],
     *      etc.
     * ).
     *
     * Please respect lower caml case naming convention for property names
     */
    public function __construct($role, $resource, $changeSet)
    {
        $this->role = $role;
        $this->changeSet = $changeSet;
        $this->details = [
            'role' => [
                'name' => $role->getTranslationKey(),
                'changeSet' => $changeSet,
            ],
            'workspace' => [
                'name' => $resource->getWorkspace() ? $resource->getWorkspace()->getName() : ' - ',
            ],
            'resource' => [
                'name' => $resource->getName(),
                'path' => $resource->getPathForDisplay(),
                'id' => $resource->getId(),
                'resourceType' => $resource->getResourceType()->getName(),
            ],
        ];

        parent::__construct(
            self::ACTION,
            $this->details,
            null,
            null,
            $resource,
            $role,
            $resource->getWorkspace()
        );
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return;
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
        $userIds = [];
        $roleUsers = $this->role->getUsers();
        foreach ($roleUsers as $user) {
            array_push($userIds, $user->getId());
        }
        $roleGroups = $this->role->getGroups();
        foreach ($roleGroups as $group) {
            $userIds = array_merge($userIds, $group->getUserIds());
        }
        $userIds = array_unique($userIds);

        return $userIds;
    }

    /**
     * Get excludeUsers array of user ids.
     *
     * @return array
     */
    public function getExcludeUserIds()
    {
        return [];
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
        return;
    }

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify()
    {
        if (!$this->changeSet || !isset($this->changeSet['mask'])) {
            return false;
        }
        if ('ROLE_ANONYMOUS' === $this->role->getName() || 'ROLE_USER' === $this->role->getName()) {
            return false;
        }

        $oldState = $this->changeSet['mask'][0];
        $newState = $this->changeSet['mask'][1];

        return 0 === $oldState % 2 && 1 === $newState % 2;
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
}
