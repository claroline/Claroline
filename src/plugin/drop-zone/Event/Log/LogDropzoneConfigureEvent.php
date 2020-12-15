<?php

namespace Claroline\DropZoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\DropZoneBundle\Entity\Dropzone;

class LogDropzoneConfigureEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_dropzone-dropzone_configure';

    /**
     * @param Dropzone $dropzone
     * @param array    $changeSet
     */
    public function __construct(Dropzone $dropzone, $changeSet)
    {
        $details = [
            'dropzone' => [
                'id' => $dropzone->getId(),
                'changeSet' => $changeSet,
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
}
