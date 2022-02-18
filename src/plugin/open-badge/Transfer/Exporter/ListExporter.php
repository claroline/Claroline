<?php

namespace Claroline\OpenBadgeBundle\Transfer\Exporter;

use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['badge', 'list'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }

    protected static function getClass(): string
    {
        return BadgeClass::class;
    }
}
