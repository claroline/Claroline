<?php

namespace Claroline\TransferBundle\Transfer\Exporter;

use Claroline\AppBundle\API\Options;

abstract class AbstractExporter implements ExporterInterface
{
    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        if (in_array(Options::WORKSPACE_IMPORT, $options)) {
            return false;
        }

        return in_array($format, ['json', 'csv']);
    }

    public function getBatchSize(): int
    {
        return 100;
    }

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array
    {
        return [];
    }
}
