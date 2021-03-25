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

use Claroline\CoreBundle\Entity\Resource\ResourceNode;

class LogResourcePublishEvent extends LogGenericEvent implements NotifiableInterface
{
    use ResourceNotifiableTrait;
    const ACTION = 'resource-publish';

    /**
     * Constructor.
     */
    public function __construct(ResourceNode $node, array $usersToNotify = [])
    {
        $this->usersToNotify = $usersToNotify;
        $this->node = $node;

        parent::__construct(
            self::ACTION,
            [
                'resource' => [
                    'name' => $node->getName(),
                    'path' => $node->getPathForCreationLog(),
                    'guid' => $node->getUuid(),
                    'resourceType' => $node->getResourceType()->getName(),
                ],
                'workspace' => [
                    'id' => $node->getWorkspace() ? $node->getWorkspace()->getId() : null,
                    'guid' => $node->getWorkspace() ? $node->getWorkspace()->getUuid() : null,
                    'name' => $node->getWorkspace() ? $node->getWorkspace()->getName() : ' - ',
                ],
                'owner' => [
                    'lastName' => $node->getCreator()->getLastName(),
                    'firstName' => $node->getCreator()->getFirstName(),
                ],
            ],
            null,
            null,
            $node,
            null,
            $node->getWorkspace(),
            $node->getCreator()
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
        return [$this->node->getCreator()->getId()];
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
}
