<?php

namespace Claroline\CoreBundle\API\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Manager\FileManager;
use Symfony\Component\HttpFoundation\File\File;

class PublicFileValidator implements ValidatorInterface
{
    /** @var FileManager */
    private $fileManager;

    private static $DISALLOWED_EXTENSIONS = [
        'php',
        'sh',
    ];

    public function __construct(FileManager $fileManager)
    {
        $this->fileManager = $fileManager;
    }

    public static function getClass(): string
    {
        return PublicFile::class;
    }

    public function validate($data, $mode, array $options = []): array
    {
        /** @var File $tmpFile */
        $tmpFile = $options['file'];
        if (empty($tmpFile)) {
            return [
                'path' => '/',
                'message' => 'No file uploaded.',
            ];
        }

        if (0 === filesize($tmpFile)) {
            return [
                'path' => '/',
                'message' => 'Empty file.',
            ];
        }

        if ($this->fileManager->isStorageFull()) {
            // platform has limited storage and it's full, we cannot upload anything else
            return [
                'path' => '/',
                'message' => 'Platform max storage reached.',
            ];
        }

        $extension = $tmpFile->guessExtension();
        if (in_array($extension, static::$DISALLOWED_EXTENSIONS)) {
            return [
                'path' => '/',
                'message' => 'The file type is not allowed.',
            ];
        }

        return [];
    }

    public function getUniqueFields(): array
    {
        return [];
    }
}
