<?php

namespace Claroline\VideoPlayerBundle\Component\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterTrait;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\VideoPlayerBundle\Entity\Video;
use Symfony\Component\HttpFoundation\File\File;

final class VideoResource extends ResourceComponent implements DownloadableResourceInterface, EvaluatedResourceInterface, FileAdapterInterface
{
    use FileAdapterTrait;

    public function __construct(
        private readonly FileManager $fileManager,
        private readonly ObjectManager $om
    ) {
    }

    public static function getName(): string
    {
        return 'video';
    }

    public static function getClass(): string
    {
        return Video::class;
    }

    public static function supportsScore(): bool
    {
        return false;
    }

    public static function supportsAttempts(): bool
    {
        return false;
    }

    public function supportsFile(File $file): int
    {
        if (str_starts_with($file->getMimeType(), 'video')) {
            return FileAdapterInterface::SUPPORTED;
        }

        return FileAdapterInterface::UNSUPPORTED;
    }

    public function fromFile(File $file): ?array
    {
        return [];
    }

    public function requireAdapter(): bool
    {
        return true;
    }
}
