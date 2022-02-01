<?php

namespace Claroline\CoreBundle\API\Validator;

use Claroline\AppBundle\API\ValidatorInterface;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Manager\FileManager;

class PublicFileValidator implements ValidatorInterface
{
    /** @var FileManager */
    private $fileManager;

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
        if ($this->fileManager->isStorageFull()) {
            // platform has limited storage and it's full, we cannot upload anything else
            return [
                'path' => '/',
                'message' => 'Platform max storage reached.',
            ];
        }

        return [];
    }

    public function getUniqueFields(): array
    {
        return [];
    }
}
