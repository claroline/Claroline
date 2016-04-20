<?php

namespace Icap\DropzoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\DropzoneBundle\Entity\Dropzone;

class LogDropzoneConfigureEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_dropzone-dropzone_configure';

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
