<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Revision;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DocumentSerializer
{
    use SerializerTrait;

    private ObjectRepository $documentRepo;
    private ObjectRepository $dropRepo;
    private ObjectRepository $revisionRepo;
    private ObjectRepository $resourceNodeRepo;

    public function __construct(
        private readonly RevisionSerializer $revisionSerializer,
        private readonly ResourceNodeSerializer $resourceSerializer,
        private readonly UserSerializer $userSerializer,
        private readonly TokenStorageInterface $tokenStorage,
        ObjectManager $om
    ) {
        $this->documentRepo = $om->getRepository(Document::class);
        $this->dropRepo = $om->getRepository(Drop::class);
        $this->revisionRepo = $om->getRepository(Revision::class);
        $this->resourceNodeRepo = $om->getRepository(ResourceNode::class);
    }

    public function getName(): string
    {
        return 'dropzone_document';
    }

    public function getClass(): string
    {
        return Document::class;
    }

    public function serialize(Document $document, array $options = []): array
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
            'revision' => $document->getRevision() ?
                $this->revisionSerializer->serialize($document->getRevision(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'isManager' => $document->getIsManager(),
        ];
    }

    public function deserialize(string $class, array $data): Document
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
