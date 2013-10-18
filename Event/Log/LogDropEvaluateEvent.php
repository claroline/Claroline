<?php

namespace Icap\DropzoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;

class LogDropEvaluateEvent extends AbstractLogResourceEvent {

    const ACTION = 'resource-icap_dropzone-drop_evaluate';

    /**
     * @param Dropzone $dropzone
     * @param Drop $drop
     */
    public function __construct(Dropzone $dropzone, Drop $drop, $grade)
    {
        $details = array(
            'dropzone'  => $dropzone->getId(),
            'drop' => $drop->getId(),
            'result' => $grade
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