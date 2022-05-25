<?php

namespace Claroline\OpenBadgeBundle\Transfer\Exporter;

use Claroline\AppBundle\API\Options;
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

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The badge id', [], 'schema'),
                ], [
                    'name' => 'name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The badge name', [], 'schema'),
                ], [
                    'name' => 'description',
                    'type' => 'text',
                    'description' => $this->translator->trans('The badge description', [], 'schema'),
                ], [
                    'name' => 'image.url',
                    'type' => 'text',
                    'description' => $this->translator->trans('The badge image URL', [], 'schema'),
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

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array
    {
        $extraDef = parent::getExtraDefinition($options, $extra);

        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            $extraDef['fields'][] = [
                'name' => 'workspace',
                'label' => $this->translator->trans('workspace', [], 'platform'),
                'type' => 'workspace',
            ];
        }

        return $extraDef;
    }
}
