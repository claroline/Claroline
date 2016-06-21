<?php

namespace Innova\CollecticielBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Innova\CollecticielBundle\Entity\Criterion;
use Innova\CollecticielBundle\Entity\Dropzone;

class LogCriterionCreateEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-innova_collecticiel-criterion_create';

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
