<?php

namespace Claroline\TransferBundle\Transfer\Importer;

use Claroline\AppBundle\API\Options;

abstract class AbstractImporter implements ImporterInterface
{
    public const MODE_CREATE = 'create';
    public const MODE_UPDATE = 'update';
    public const MODE_DELETE = 'delete';
    public const MODE_DEFAULT = 'default';

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        if (in_array(Options::WORKSPACE_IMPORT, $options)) {
            return false;
        }

        return in_array($format, ['json', 'csv']);
    }

    public function getBatchSize(): int
    {
        return 1;
    }

    public function getMode(): string
    {
        return self::MODE_DEFAULT;
    }

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array
    {
        return [];
    }
}
