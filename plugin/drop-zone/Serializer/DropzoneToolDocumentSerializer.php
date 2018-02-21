<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\DropZoneBundle\Entity\DropzoneToolDocument;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.dropzone.tool.document")
 * @DI\Tag("claroline.serializer")
 */
class DropzoneToolDocumentSerializer
{
    private $dropzoneToolDocumentRepo;
    private $dropzoneToolRepo;
    private $documentRepo;

    /**
     * DropzoneToolDocumentSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->dropzoneToolDocumentRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\DropzoneToolDocument');
        $this->dropzoneToolRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\DropzoneTool');
        $this->documentRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Document');
    }

    /**
     * @param DropzoneToolDocument $dropzoneToolDocument
     *
     * @return array
     */
    public function serialize(DropzoneToolDocument $dropzoneToolDocument)
    {
        return [
            'id' => $dropzoneToolDocument->getUuid(),
            'document' => $dropzoneToolDocument->getDocument()->getUuid(),
            'tool' => $dropzoneToolDocument->getTool()->getUuid(),
            'data' => $dropzoneToolDocument->getData(),
        ];
    }

    /**
     * @param string $class
     * @param array  $data
     *
     * @return DropzoneToolDocument
     */
    public function deserialize($class, $data)
    {
        $dropzoneToolDocument = $this->dropzoneToolDocumentRepo->findOneBy(['uuid' => $data['id']]);

        if (empty($dropzoneToolDocument)) {
            $dropzoneToolDocument = new DropzoneToolDocument();
            $dropzoneToolDocument->setUuid($data['id']);
            $tool = $this->dropzoneToolRepo->findOneBy(['uuid' => $data['tool']]);
            $dropzoneToolDocument->setTool($tool);
            $document = $this->documentRepo->findOneBy(['uuid' => $data['document']]);
            $dropzoneToolDocument->setDocument($document);
        }
        if (isset($data['data'])) {
            $dropzoneToolDocument->setData($data['data']);
        }

        return $dropzoneToolDocument;
    }
}
