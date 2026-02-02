<?php

namespace Claroline\CoreBundle\Listener\Resource\Types;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterTrait;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Entity\Resource\File as FileResource;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Integrates the File resource into Claroline.
 */
final class FileListener extends ResourceComponent implements DownloadableResourceInterface, EvaluatedResourceInterface, FileAdapterInterface
{
    use FileAdapterTrait;

    public function __construct(
        private readonly PlatformConfigurationHandler $config,
        private readonly ObjectManager $om,
        private readonly FileManager $fileManager
    ) {
    }

    public static function getName(): string
    {
        return 'file';
    }

    public static function getClass(): string
    {
        return FileResource::class;
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
        $blacklist = $this->config->getParameter('file_blacklist');
        if (empty($blacklist) || !in_array($file->getMimeType(), $blacklist)) {
            return FileAdapterInterface::SUPPORTED_PARTIAL;
        }

        return FileAdapterInterface::UNSUPPORTED;
    }

    public function fromFile(File $file): ?array
    {
        return ['meta' => ['downloadable' => true]];
    }

    public function requireAdapter(): bool
    {
        return true;
    }
}
