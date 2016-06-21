<?php

namespace Icap\DropzoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\DropzoneBundle\Entity\Criterion;
use Icap\DropzoneBundle\Entity\Dropzone;

class LogCriterionDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_dropzone-criterion_delete';

    /**
     * @param Dropzone  $dropzone
     * @param Criterion $criterion
     */
    public function __construct(Dropzone $dropzone, Criterion $criterion)
    {
        $details = array(
            'dropzone' => array(
                'id' => $dropzone->getId(),
            ),
            'criterion' => array(
                'id' => $criterion->getId(),
                'instruction' => $criterion->getInstruction(),
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
