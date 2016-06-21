<?php

namespace Innova\CollecticielBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Innova\CollecticielBundle\Entity\Drop;
use Innova\CollecticielBundle\Entity\Dropzone;

class LogDropStartEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-innova_collecticiel-drop_start';

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
