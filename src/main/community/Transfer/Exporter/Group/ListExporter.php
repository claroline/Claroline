<?php

namespace Claroline\CommunityBundle\Transfer\Exporter\Group;

use Claroline\CoreBundle\Entity\Group;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListExporter extends AbstractListExporter
{
    public static function getAction(): array
    {
        return ['group', 'list'];
    }

    protected static function getClass(): string
    {
        return Group::class;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group id', [], 'schema'),
                ], [
                    'name' => 'name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group name', [], 'schema'),
                ],
            ],
        ];
    }

    protected function getAvailableFilters(): array
    {
        return [
            [
                'name' => 'name',
                'label' => $this->translator->trans('name', [], 'platform'),
                'type' => 'string',
            ],
        ];
    }

    protected function getAvailableSortBy(): array
    {
        return [
            [
                'name' => 'name',
                'label' => $this->translator->trans('name', [], 'platform'),
            ],
        ];
    }
}
