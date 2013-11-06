<?php

namespace Icap\DropzoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;

class LogDropzoneUpdate extends AbstractLogResourceEvent {

    const ACTION = 'resource-icap_dropzone-dropzone_update';

    /**
     * @param Dropzone $dropzone
     */
    public function __construct(Dropzone $dropzone)
    {
        $details = array(
            'dropzoneId'  => $dropzone->getId(),
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