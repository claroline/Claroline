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
    protected $dropzone;
    protected $details;
    private $role_manager;

    /**
     * @param Wiki         $wiki
     * @param Section      $section
     * @param Contribution $contribution
     */
    public function __construct(Dropzone $dropzone, Drop $drop, Correction $correction, $roleManager)
    {
        $this->dropzone = $dropzone;
        $this->role_manager = $roleManager;
        $this->details = [
            'report' => [
                'drop' => $drop,
                'correction' => $correction,
                'report_comment' => $correction->getReportComment(),
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
        // In order to get users with the manager role.
        //getting the  workspace.

        $ResourceNode = $this->dropzone->getResourceNode();
        $workspace = $ResourceNode->getWorkspace();
        // getting the  Manager role
        $role = $this->role_manager->getManagerRole($workspace);

        // to finaly have the users.
        $users = $role->getUsers();
        $ids = [];
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
