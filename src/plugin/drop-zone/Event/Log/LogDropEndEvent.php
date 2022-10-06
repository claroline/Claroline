<?php

namespace Claroline\DropZoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;

class LogDropEndEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-claroline_dropzone-drop_end';

    /** @var Dropzone */
    protected $dropzone;

    public function __construct(Dropzone $dropzone, Drop $drop)
    {
        $this->dropzone = $dropzone;

        $documentsDetails = [];
        foreach ($drop->getDocuments() as $document) {
            $documentsDetails[] = $document->toArray();
        }

        $details = [
            'dropzone' => [
                'id' => $dropzone->getId(),
            ],
            'drop' => [
                'id' => $drop->getId(),
                'documents' => $documentsDetails,
            ],
        ];

        parent::__construct($dropzone->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [LogGenericEvent::DISPLAYED_WORKSPACE];
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
     */
    public function getSendToFollowers()
    {
        //Reports are only reported to user witch have the manager role
        return false;
    }

    /**
     * Get includeUsers array of user ids.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        $resourceNode = $this->dropzone->getResourceNode();
        $workspace = $resourceNode->getWorkspace();

        $ids = [];

        $role = $workspace->getManagerRole();
        if ($role) {
            $users = $role->getUsers();
            foreach ($users as $user) {
                $ids[] = $user->getId();
            }
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
        return 'dropzone';
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $notificationDetails = array_merge($this->details, []);
        $notificationDetails['resource'] = [
            'id' => $this->dropzone->getId(),
            'name' => $this->resource->getName(),
            'type' => $this->resource->getResourceType()->getName(),
        ];

        return $notificationDetails;
    }

    /**
     * Get if event is allowed to create notification or not.
     *
     * @return bool
     */
    public function isAllowedToNotify()
    {
        $allowed = false;
        if (null !== $this->dropzone && $this->dropzone->getNotifyOnDrop()) {
            $allowed = true;
        }

        return $allowed;
    }
}
