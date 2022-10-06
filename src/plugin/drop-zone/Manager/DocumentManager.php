<?php

namespace Claroline\DropZoneBundle\Manager;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Document;
use Claroline\DropZoneBundle\Entity\Drop;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Entity\Revision;
use Claroline\DropZoneBundle\Event\Log\LogDocumentCreateEvent;
use Claroline\DropZoneBundle\Event\Log\LogDocumentDeleteEvent;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DocumentManager
{
    /** @var string */
    private $filesDir;
    /** @var Filesystem */
    private $fileSystem;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    public function __construct(
        string $filesDir,
        Filesystem $fileSystem,
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om,
        SerializerProvider $serializer
    ) {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->serializer = $serializer;
    }

    /**
     * Creates a Document.
     *
     * @deprecated use crud instead
     */
    public function createDocument(Drop $drop, User $user, string $documentType, $documentData, ?Revision $revision = null, ?bool $isManager = false): Document
    {
        $document = new Document();
        $document->setDrop($drop);
        $document->setUser($user);
        $document->setDropDate(new \DateTime());
        $document->setType($documentType);
        $document->setRevision($revision);
        $document->setIsManager($isManager);

        if (Document::DOCUMENT_TYPE_RESOURCE === $document->getType()) {
            $resourceNode = $this->om->getRepository(ResourceNode::class)->findOneBy(['uuid' => $documentData['id']]);
            $document->setData($resourceNode);
        } else {
            $document->setData($documentData);
        }

        $this->om->persist($document);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new LogDocumentCreateEvent($drop->getDropzone(), $drop, $document), 'log');

        return $document;
    }

    /**
     * Creates Files Documents.
     *
     * @param Revision $revision
     * @param bool     $isManager
     *
     * @return array
     */
    public function createFilesDocuments(Drop $drop, User $user, array $files, Revision $revision = null, $isManager = false)
    {
        $documents = [];
        $documentEntities = [];
        $currentDate = new \DateTime();
        $dropzone = $drop->getDropzone();
        $this->om->startFlushSuite();

        foreach ($files as $file) {
            $document = new Document();
            $document->setDrop($drop);
            $document->setUser($user);
            $document->setDropDate($currentDate);
            $document->setType(Document::DOCUMENT_TYPE_FILE);
            $document->setRevision($revision);
            $document->setIsManager($isManager);
            $data = $this->registerUploadedFile($dropzone, $file);
            $document->setFile($data);
            $this->om->persist($document);
            $documentEntities[] = $document;
            $documents[] = $this->serializer->serialize($document);
        }
        $this->om->endFlushSuite();

        //tracking for each document, after flush
        foreach ($documentEntities as $entity) {
            $this->eventDispatcher->dispatch(new LogDocumentCreateEvent($drop->getDropzone(), $drop, $entity), 'log');
        }

        return $documents;
    }

    /**
     * Deletes a Document.
     *
     * @deprecated use crud instead
     */
    public function deleteDocument(Document $document)
    {
        if (Document::DOCUMENT_TYPE_FILE === $document->getType()) {
            $data = $document->getFile();

            if (isset($data['url'])) {
                $this->fileSystem->remove($this->filesDir.DIRECTORY_SEPARATOR.$data['url']);
            }
        }
        $this->om->remove($document);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new LogDocumentDeleteEvent($document->getDrop()->getDropzone(), $document->getDrop(), $document), 'log');
    }

    private function registerUploadedFile(Dropzone $dropzone, UploadedFile $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hashName = Uuid::uuid4()->toString();
        $dir = $this->filesDir.$ds.'dropzone'.$ds.$dropzone->getUuid();
        $fileName = $hashName.'.'.$file->getClientOriginalExtension();

        $file->move($dir, $fileName);

        return [
            'name' => $file->getClientOriginalName(),
            'mimeType' => $file->getClientMimeType(),
            'url' => 'dropzone'.$ds.$dropzone->getUuid().$ds.$fileName,
        ];
    }
}
