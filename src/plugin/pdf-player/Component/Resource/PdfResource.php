<?php

namespace Claroline\PdfPlayerBundle\Component\Resource;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Component\Resource\DownloadableResourceInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterInterface;
use Claroline\CoreBundle\Component\Resource\FileAdapterTrait;
use Claroline\CoreBundle\Component\Resource\ResourceComponent;
use Claroline\CoreBundle\Manager\FileManager;
use Claroline\EvaluationBundle\Component\Resource\EvaluatedResourceInterface;
use Claroline\PdfPlayerBundle\Entity\Pdf;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Integrates PDF files into Claroline.
 */
final class PdfResource extends ResourceComponent implements DownloadableResourceInterface, EvaluatedResourceInterface, FileAdapterInterface
{
    use FileAdapterTrait;

    public function __construct(
        private readonly FileManager $fileManager,
        private readonly ObjectManager $om
    ) {
    }

    public static function getName(): string
    {
        return 'pdf';
    }

    public static function getClass(): string
    {
        return Pdf::class;
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
        if ('application/pdf' === $file->getMimeType()) {
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
