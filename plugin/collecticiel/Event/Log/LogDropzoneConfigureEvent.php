<?php

namespace Innova\CollecticielBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Innova\CollecticielBundle\Entity\Dropzone;

class LogDropzoneConfigureEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-innova_collecticiel-dropzone_configure';

    /**
     * @param Dropzone $dropzone
     * @param array    $changeSet
     */
    public function __construct(Dropzone $dropzone, $changeSet)
    {
        $details = array(
            'dropzone' => array(
                'id' => $dropzone->getId(),
                'changeSet' => $changeSet,
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
}
