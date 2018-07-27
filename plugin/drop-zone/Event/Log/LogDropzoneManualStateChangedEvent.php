<?php

namespace Claroline\DropZoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Claroline\DropZoneBundle\Entity\Dropzone;

class LogDropzoneManualStateChangedEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-claroline_dropzone-dropzone_manual_state_changed';
    protected $dropzone;
    protected $newState;
    protected $details;
    private $userIds = [];

    /**
     * @param Wiki         $wiki
     * @param Section      $section
     * @param Contribution $contribution
     */
    public function __construct(Dropzone $dropzone, $newstate, $userIds)
    {
        $this->dropzone = $dropzone;
        $this->newState = $newstate;
        $this->userIds = $userIds;
        $this->details = [
                'newState' => $newstate,
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

    public function getDropzone()
    {
        return $this->dropzone;
    }

    /**
     * Get sendToFollowers boolean.
     *
     * @return bool
     */
    public function getSendToFollowers()
    {
        return true;
    }

    /**
     * Get includeUsers array of user ids.
     * Reports are only reported to user witch have the manager role.
     *
     * @return array
     */
    public function getIncludeUserIds()
    {
        return $this->userIds;
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
