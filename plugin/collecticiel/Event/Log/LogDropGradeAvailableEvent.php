<?php

namespace Innova\CollecticielBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\NotifiableInterface;
use Innova\CollecticielBundle\Entity\Drop;
use Innova\CollecticielBundle\Entity\Dropzone;

class LogDropGradeAvailableEvent extends AbstractLogResourceEvent implements NotifiableInterface
{
    const ACTION = 'resource-innova_collecticiel-drop_grade_available';
    protected $dropzone;
    protected $drop;
    protected $details;

    /**
     * @param \Innova\CollecticielBundle\Entity\Dropzone $dropzone
     * @param \Innova\CollecticielBundle\Entity\Drop     $drop
     *
     * @internal param \Innova\CollecticielBundle\Event\Log\Wiki $wiki
     * @internal param \Innova\CollecticielBundle\Event\Log\Section $section
     * @internal param \Innova\CollecticielBundle\Event\Log\Contribution $contribution
     */
    public function __construct(Dropzone $dropzone, Drop $drop)
    {
        $this->dropzone = $dropzone;
        $this->drop = $drop;
        $this->details = array(
                'drop' => $drop,
            'dropGrade' => $drop->getCalculatedGrade(),
            'resultMax' => 20,
            'dropzoneId' => $dropzone->getId(),
                'dropId' => $drop->getId(),
        );

        parent::__construct($dropzone->getResourceNode(), $this->details);
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
     * @return bool
     */
    public function getSendToFollowers()
    {
        // notify only the drop's owner.
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
        // notify only the drop's owner.
        $ids = array();
        $id = $this->drop->getUser()->getId();
        array_push($ids, $id);

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
        return true;
    }
}
