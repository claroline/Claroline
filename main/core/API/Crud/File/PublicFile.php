<?php

namespace Claroline\CoreBundle\API\Crud\File;

use Claroline\AppBundle\Event\Crud\CreateEvent;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\FileUtilities;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem as SymfonyFileSystem;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @DI\Service("claroline.crud.publicfile")
 * @DI\Tag("claroline.crud")
 */
class PublicFile
{
    /** @var ObjectManager */
    private $om;

    /** @var FileUtilities */
    private $utils;

    /**
     * @DI\InjectParams({
     *     "filesDir"       = @DI\Inject("%claroline.param.files_directory%"),
     *     "fileSystem"     = @DI\Inject("filesystem"),
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "publicFilesDir" = @DI\Inject("%claroline.param.public_files_directory%"),
     *     "tokenStorage"   = @DI\Inject("security.token_storage"),
     *     "fileUtils"      = @DI\Inject("claroline.utilities.file")
     * })
     */
    public function __construct(
        $filesDir,
        SymfonyFileSystem $fileSystem,
        ObjectManager $om,
        $publicFilesDir,
        TokenStorageInterface $tokenStorage,
        FileUtilities $fileUtils
    ) {
        $this->filesDir = $filesDir;
        $this->fileSystem = $fileSystem;
        $this->om = $om;
        $this->publicFilesDir = $publicFilesDir;
        $this->tokenStorage = $tokenStorage;
        $this->fileUtils = $fileUtils;
    }

    /**
     * @DI\Observe("crud_pre_create_object_claroline_corebundle_entity_file_publicfile")
     *
     * @param CreateEvent $event
     */
    public function preCreate(CreateEvent $event)
    {
        $publicFile = $event->getObject();
        $options = $event->getOptions();
        $tmpFile = $options['file'];

        $fileName = empty($tmpFile->getClientOriginalName()) ?
            $tmpFile->getFileName() :
            $tmpFile->getClientOriginalName();
        $directoryName = $this->fileUtils->getActiveDirectoryName();
        $size = filesize($tmpFile);
        $mimeType = empty($tmpFile->getClientMimeType()) ?
            $tmpFile->getMimeType() :
            $tmpFile->getClientMimeType();
        $extension = empty($tmpFile->getClientOriginalExtension()) ?
            $tmpFile->guessExtension() :
            $tmpFile->getClientOriginalExtension();
        $hashName = Uuid::uuid4()->toString().'.'.$extension;
        $prefix = 'data'.DIRECTORY_SEPARATOR.$directoryName;
        $url = $prefix.DIRECTORY_SEPARATOR.$hashName;

        $publicFile->setDirectoryName($directoryName);
        $publicFile->setFilename($fileName);
        $publicFile->setSize($size);
        $publicFile->setMimeType($mimeType);
        $publicFile->setCreationDate(new \DateTime());
        $publicFile->setUrl($url);

        if ($this->tokenStorage->getToken() && $user = 'anon.' !== $this->tokenStorage->getToken()->getUser()) {
            $user = $this->tokenStorage->getToken()->getUser();
            $publicFile->setCreator($user);
        }

        $tmpFile->move($this->filesDir.DIRECTORY_SEPARATOR.$prefix, $hashName);
    }
}
