<?php

namespace Icap\DropzoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;

class LogDropStartEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_dropzone-drop_start';

    /**
     * @param Dropzone $dropzone
     * @param Drop     $drop
     */
    public function __construct(Dropzone $dropzone, Drop $drop)
    {
        $details = array(
            'dropzone' => array(
                'id' => $dropzone->getId(),
            ),
            'drop' => array(
                'id' => $drop->getId(),
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
