<?php

namespace Innova\CollecticielBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Innova\CollecticielBundle\Entity\Document;
use Innova\CollecticielBundle\Entity\Drop;
use Innova\CollecticielBundle\Entity\Dropzone;

class LogDocumentDeleteEvent extends AbstractLogResourceEvent
{
    const ACTION = 'resource-innova_collecticiel-document_delete';

    /**
     * @param Dropzone $dropzone
     * @param Drop     $drop
     * @param Document $document
     */
    public function __construct(Dropzone $dropzone, Drop $drop, Document $document)
    {
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
            ),
            'document' => $document->toArray(),
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
