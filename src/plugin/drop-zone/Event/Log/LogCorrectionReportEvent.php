<?php

namespace Claroline\DropZoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;

class LogCorrectionReportEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-claroline_dropzone-correction_report';

    /** @var Dropzone */
    protected $dropzone;
    /** @var array */
    protected $details;

    public function __construct(Dropzone $dropzone, Drop $drop, Correction $correction)
    {
        $this->dropzone = $dropzone;
        $this->details = [
            'report' => [
                'drop' => $drop,
                'correction' => $correction,
                'report_comment' => $correction->getReportedComment(),
                'dropzoneId' => $dropzone->getId(),
                'dropId' => $drop->getId(),
                'correctionId' => $correction->getId(),
            ],
        ];

        parent::__construct($dropzone->getResourceNode(), $this->details);
    }

    /**
     * @return array
     */
    public static function getRestriction()
    {
        return [self::DISPLAYED_WORKSPACE];
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
     * Reports are only reported to user witch have the manager role.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        $resourceNode = $this->dropzone->getResourceNode();
        $workspace = $resourceNode->getWorkspace();

        $ids = [];
        // getting the  Manager role
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
     * Get iconTypeUrl string.
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
        return true;
    }
}
