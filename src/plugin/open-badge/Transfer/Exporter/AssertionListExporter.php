<?php

namespace Claroline\OpenBadgeBundle\Transfer\Exporter;

use Claroline\OpenBadgeBundle\Entity\Assertion;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class AssertionListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['badge', 'list_assertions'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }

    protected static function getClass(): string
    {
        return Assertion::class;
    }

    protected function getHiddenFilters(): array
    {
        return [
            // do not filter disabled and deleted users for now
            'userDisabled' => 'all',
        ];
    }
}
