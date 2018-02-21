<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.serializer.dropzone.document")
 * @DI\Tag("claroline.serializer")
 */
class DocumentSerializer
{
    private $dropzoneToolDocumentSerializer;
    private $resourceSerializer;
    private $userSerializer;
    private $tokenStorage;

    private $documentRepo;
    private $dropRepo;
    private $resourceNodeRepo;

    /**
     * DocumentSerializer constructor.
     *
     * @DI\InjectParams({
     *     "dropzoneToolDocumentSerializer" = @DI\Inject("claroline.serializer.dropzone.tool.document"),
     *     "resourceSerializer"             = @DI\Inject("claroline.serializer.resource_node"),
     *     "userSerializer"                 = @DI\Inject("claroline.serializer.user"),
     *     "tokenStorage"                   = @DI\Inject("security.token_storage"),
     *     "om"                             = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param DropzoneToolDocumentSerializer $dropzoneToolDocumentSerializer
     * @param ResourceNodeSerializer         $resourceSerializer
     * @param UserSerializer                 $userSerializer
     * @param TokenStorageInterface          $tokenStorage
     * @param ObjectManager                  $om
     */
    public function __construct(
        DropzoneToolDocumentSerializer $dropzoneToolDocumentSerializer,
        ResourceNodeSerializer $resourceSerializer,
        UserSerializer $userSerializer,
        TokenStorageInterface $tokenStorage,
        ObjectManager $om
    ) {
        $this->dropzoneToolDocumentSerializer = $dropzoneToolDocumentSerializer;
        $this->resourceSerializer = $resourceSerializer;
        $this->userSerializer = $userSerializer;
        $this->tokenStorage = $tokenStorage;

        $this->documentRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Document');
        $this->dropRepo = $om->getRepository('Claroline\DropZoneBundle\Entity\Drop');
        $this->resourceNodeRepo = $om->getRepository('Claroline\CoreBundle\Entity\Resource\ResourceNode');
    }

    /**
     * @param Document $document
     *
     * @return array
     */
    public function serialize(Document $document)
    {
        return [
            'id' => $document->getUuid(),
            'type' => $document->getType(),
            'data' => Document::DOCUMENT_TYPE_RESOURCE === $document->getType() ?
                $this->resourceSerializer->serialize($document->getData()) :
                $document->getData(),
            'drop' => $document->getDrop()->getUuid(),
            'user' => $document->getUser() ? $this->userSerializer->serialize($document->getUser()) : null,
            'dropDate' => $document->getDropDate() ? $document->getDropDate()->format('Y-m-d H:i') : null,
            'toolDocuments' => $this->getToolDocuments($document),
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

        return $document;
    }
}
