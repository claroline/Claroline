<?php

namespace Innova\CollecticielBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Innova\CollecticielBundle\Entity\Correction;
use Innova\CollecticielBundle\Entity\Drop;
use Innova\CollecticielBundle\Entity\Dropzone;

class LogCorrectionUpdateEvent extends AbstractLogResourceEvent implements PotentialEvaluationEndInterface
{
    const ACTION = 'resource-innova_collecticiel-correction_update';

    private $correction;

    /**
     * @param Dropzone   $dropzone
     * @param Drop       $drop
     * @param Correction $correction
     */
    public function __construct(Dropzone $dropzone, Drop $drop, Correction $correction)
    {
        $this->correction = $correction;

        $documentsDetails = array();
        foreach ($drop->getDocuments() as $document) {
            $documentsDetails[] = $document->toArray();
        }

        $details = array(
            'dropzone' => array(
                'id' => $dropzone->getId(),
            ),
            'drop' => array(
                'id' => $drop->getId(),
                'documents' => $documentsDetails,
                'owner' => array(
                    'id' => $drop->getUser()->getId(),
                    'lastName' => $drop->getUser()->getLastName(),
                    'firstName' => $drop->getUser()->getFirstName(),
                    'username' => $drop->getUser()->getUsername(),
                ),
            ),
            'correction' => $correction->toArray(true),
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
