<?php

namespace Icap\DropzoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\DropzoneBundle\Entity\Correction;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;

class LogCorrectionUpdateEvent extends AbstractLogResourceEvent implements PotentialEvaluationEndInterface {

    const ACTION = 'resource-icap_dropzone-correction_start';

    private $correction;

    /**
     * @param Dropzone $dropzone
     * @param Drop $drop
     */
    public function __construct(Dropzone $dropzone, Drop $drop, Correction $correction)
    {
        $this->correction = $correction;

        $details = array(
            'dropzoneId'  => $dropzone->getId(),
            'dropId' => $drop->getId(),
            'correctionId' => $correction->getId(),
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

    /**
     * @return array
     */
    public function getCorrection()
    {
        return $this->correction;
    }
}