<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Revision;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DocumentSerializer
{
    use SerializerTrait;

    private $dropzoneToolDocumentSerializer;
    private $revisionSerializer;
    private $resourceSerializer;
    private $userSerializer;
    private $tokenStorage;

    private $documentRepo;
    private $dropRepo;
    private $revisionRepo;
    private $resourceNodeRepo;

    public function __construct(
        DropzoneToolDocumentSerializer $dropzoneToolDocumentSerializer,
        RevisionSerializer $revisionSerializer,
        ResourceNodeSerializer $resourceSerializer,
        UserSerializer $userSerializer,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om
    ) {
        $this->dropzoneToolDocumentSerializer = $dropzoneToolDocumentSerializer;
        $this->revisionSerializer = $revisionSerializer;
        $this->resourceSerializer = $resourceSerializer;
        $this->userSerializer = $userSerializer;
        $this->tokenStorage = $tokenStorage;

        $this->documentRepo = $om->getRepository(Document::class);
        $this->dropRepo = $om->getRepository(Drop::class);
        $this->revisionRepo = $om->getRepository(Revision::class);
        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
    }

    public function getName()
    {
        return 'dropzone_document';
    }

    /**
     * @return array
     */
    public function serialize(Document $document, array $options = [])
    {
        $documentData = $document->getData();
        if (Document::DOCUMENT_TYPE_RESOURCE === $document->getType() && !empty($documentData)) {
            $documentData = $this->resourceSerializer->serialize($documentData);
        }

        return [
            'id' => $document->getUuid(),
            'type' => $document->getType(),
            'data' => $documentData,
            'drop' => $document->getDrop()->getUuid(),
            'user' => $document->getUser() ? $this->userSerializer->serialize($document->getUser()) : null,
            'dropDate' => $document->getDropDate() ? $document->getDropDate()->format('Y-m-d H:i') : null,
            'toolDocuments' => $this->getToolDocuments($document),
            'revision' => $document->getRevision() ?
                $this->revisionSerializer->serialize($document->getRevision(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'isManager' => $document->getIsManager(),
        ];
    }

    private function getToolDocuments(Document $document)
    {
        $toolDocuments = [];

        foreach ($document->getToolDocuments() as $toolDocument) {
            $toolDocuments[] = $this->dropzoneToolDocumentSerializer->serialize($toolDocument);
        }

        return $toolDocuments;
    }

    /**
     * @param string $class
     * @param array  $data
     *
     * @return Document
     */
    public function deserialize($class, $data)
    {
        $document = $this->documentRepo->findOneBy(['uuid' => $data['id']]);

        if (empty($document)) {
            $document = new Document();
            $document->setUuid($data['id']);

            /** @var Drop $drop */
            $drop = $this->dropRepo->findOneBy(['uuid' => $data['drop']]);
            $document->setDrop($drop);
            $currentUser = $this->tokenStorage->getToken()->getUser();

            if ($currentUser instanceof User) {
                $document->setUser($currentUser);
            }
            $document->setDropDate(new \DateTime());
        }
        if (isset($data['type'])) {
            $document->setType($data['type']);

            if (isset($data['data'])) {
                $documentData = Document::DOCUMENT_TYPE_RESOURCE === $document->getType() ?
                    $this->resourceNodeRepo->findOneBy(['uuid' => $data['data']]) :
                    $data['data'];
                $document->setData($documentData);
            }
        }
        if (!$document->getRevision() && isset($data['revision']['id'])) {
            $revision = $this->revisionRepo->findOneBy(['uuid' => $data['revision']['id']]);

            if ($revision) {
                $document->setRevision($revision);
            }
        }
        if (isset($data['isManager'])) {
            $document->setIsManager($data['isManager']);
        }

        return $document;
    }
}
