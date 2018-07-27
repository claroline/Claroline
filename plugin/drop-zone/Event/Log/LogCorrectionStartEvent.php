<?php

namespace Claroline\DropZoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;

class LogCorrectionStartEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-claroline_dropzone-correction_start';

    /**
     * @param Dropzone   $dropzone
     * @param Drop       $drop
     * @param Correction $correction
     */
    public function __construct(Dropzone $dropzone, Drop $drop, Correction $correction)
    {
        $documentsDetails = [];
        foreach ($drop->getDocuments() as $document) {
            $documentsDetails[] = $document->toArray();
        }

        $details = [
            'dropzone' => [
                'id' => $dropzone->getId(),
            ],
            'drop' => [
                'id' => $drop->getId(),
                'documents' => $documentsDetails,
                'owner' => [
                    'id' => $drop->getUser()->getId(),
                    'lastName' => $drop->getUser()->getLastName(),
                    'firstName' => $drop->getUser()->getFirstName(),
                    'username' => $drop->getUser()->getUsername(),
                ],
            ],
            'correction' => $correction->toArray(false),
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
