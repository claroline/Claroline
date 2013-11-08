<?php

namespace Icap\DropzoneBundle\Event\Log;

use Claroline\CoreBundle\Event\Log\AbstractLogResourceEvent;
use Claroline\CoreBundle\Event\Log\LogGenericEvent;
use Icap\DropzoneBundle\Entity\Document;
use Icap\DropzoneBundle\Entity\Drop;
use Icap\DropzoneBundle\Entity\Dropzone;

class LogDocumentDeleteEvent extends AbstractLogResourceEvent {

    const ACTION = 'resource-icap_dropzone-document_delete';

    /**
     * @param Dropzone $dropzone
     * @param Drop $drop
     */
    public function __construct(Dropzone $dropzone, Drop $drop, Document $document)
    {
        $documentsDetails = array();
        foreach ($drop->getDocuments() as $document) {
            $documentsDetails[] = $document->toJson();
        }

        $details = array(
            'dropzone'  => array(
                'id' => $dropzone->getId(),
            ),
            'drop'  => array(
                'id' => $drop->getId(),
                'documents' => $documentsDetails
            ),
            'document' => $document->toJson(),
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