<?php

namespace Icap\DropzoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;

class LogDropEndEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-icap_dropzone-drop_end';
    protected $dropzone;
    private $role_manager;

    /**
     * @param Dropzone $dropzone
     * @param Drop     $drop
     * @param $roleManager
     */
    public function __construct(Dropzone $dropzone, Drop $drop, $roleManager)
    {
        $this->dropzone = $dropzone;
        $this->role_manager = $roleManager;

        $documentsDetails = array();
        foreach ($drop->getDocuments() as $document) {
            $documentsDetails[] = $document->toArray();
        }

        $details = array(
            'dropzone' => array(
                'id' => $dropzone->getId(),
            ),
            'drop' => array(
                'id' => $drop->getId(),
                'documents' => $documentsDetails,
            ),
        );

        parent::__construct($dropzone->getResourceNode(), $details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return array(LogGenericEvent::DISPLAYED_WORKSPACE);
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
        // In order to get users with the manager role.
        //getting the  workspace.

        $ResourceNode = $this->dropzone->getResourceNode();
        $workspace = $ResourceNode->getWorkspace();
        // getting the  Manager role
        $role = $this->role_manager->getManagerRole($workspace);

        // to finaly have the users.
        $users = $role->getUsers();
        $ids = array();
        foreach ($users as $user) {
            array_push($ids, $user->getId());
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
        return 'dropzone';
    }

    /**
     * Get details.
     *
     * @return array
     */
    public function getNotificationDetails()
    {
        $notificationDetails = array_merge($this->details, array());
        $notificationDetails['resource'] = array(
            'id' => $this->dropzone->getId(),
            'name' => $this->resource->getName(),
            'type' => $this->resource->getResourceType()->getName(),
        );

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
        if ($this->dropzone != null && $this->dropzone->getNotifyOnDrop()) {
            $allowed = true;
        }

        return $allowed;
    }
}
