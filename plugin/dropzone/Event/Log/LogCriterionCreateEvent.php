<?php

namespace Icap\DropzoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\DropzoneBundle\Entity\Criterion;
use Icap\DropzoneBundle\Entity\Dropzone;

class LogCriterionCreateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-icap_dropzone-criterion_create';

    /**
     * @param Dropzone  $dropzone
     * @param mixed     $dropzoneChangeSet
     * @param Criterion $criterion
     */
    public function __construct(Dropzone $dropzone, $dropzoneChangeSet, Criterion $criterion)
    {
        $details = array(
            'dropzone' => array(
                'id' => $dropzone->getId(),
                'changeSet' => $dropzoneChangeSet,
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
