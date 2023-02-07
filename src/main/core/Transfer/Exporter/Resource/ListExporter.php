<?php

namespace Claroline\CoreBundle\Transfer\Exporter\Resource;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListExporter extends AbstractListExporter
{
    public static function getAction(): array
    {
        return ['resource', 'list'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }

    protected static function getClass(): string
    {
        return ResourceNode::class;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        $properties = [
            [
                'name' => 'id',
                'type' => 'string',
                'description' => $this->translator->trans('The resource id or uuid', [], 'schema'),
            ], [
                'name' => 'name',
                'type' => 'string',
                'description' => $this->translator->trans('The resource name', [], 'schema'),
            ], [
                'name' => 'code',
                'type' => 'string',
                'description' => $this->translator->trans('The resource code', [], 'schema'),
            ], [
                'name' => 'slug',
                'type' => 'string',
                'description' => $this->translator->trans('The resource slug', [], 'schema'),
            ], [
                'name' => 'meta.published',
                'type' => 'string',
                'description' => $this->translator->trans('The resource publication status', [], 'schema'),
            ], [
                'name' => 'meta.mimeType',
                'type' => 'string',
                'description' => $this->translator->trans('The resource mime type', [], 'schema'),
            ], [
                'name' => 'meta.description',
                'type' => 'string',
                'description' => $this->translator->trans('The resource description', [], 'schema'),
            ], [
                'name' => 'meta.created',
                'type' => 'date',
                'description' => $this->translator->trans('The resource creation date', [], 'schema'),
            ], [
                'name' => 'meta.updated',
                'type' => 'date',
                'description' => $this->translator->trans('The resource last modification date', [], 'schema'),
            ], [
                'name' => 'evaluation.required',
                'type' => 'boolean',
                'description' => $this->translator->trans('The resource is required', [], 'schema'),
            ], [
                'name' => 'evaluation.evaluated',
                'type' => 'boolean',
                'description' => $this->translator->trans('The resource is evaluated', [], 'schema'),
            ],
        ];

        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            $properties = array_merge($properties, [
                [
                    'name' => 'workspace.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace id or uuid', [], 'schema'),
                ], [
                    'name' => 'workspace.name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace name', [], 'schema'),
                ], [
                    'name' => 'workspace.code',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace code', [], 'schema'),
                ],
            ]);
        }

        return [
            'properties' => $properties,
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

    protected function getAvailableFilters(): array
    {
        return [
            [
                'name' => 'published',
                'label' => $this->translator->trans('published', [], 'platform'),
                'type' => 'boolean',
            ], [
                'name' => 'tags',
                'label' => $this->translator->trans('tags', [], 'platform'),
                'type' => 'tag',
                'options' => [
                    'objectClass' => ResourceNode::class,
                ],
            ],
        ];
    }

    protected function getAvailableSortBy(): array
    {
        return [
            [
                'name' => 'name',
                'label' => $this->translator->trans('name', [], 'platform'),
            ], [
                'name' => 'code',
                'label' => $this->translator->trans('code', [], 'platform'),
            ], [
                'name' => 'meta.created',
                'label' => $this->translator->trans('creation_date', [], 'platform'),
            ], [
                'name' => 'meta.updated',
                'label' => $this->translator->trans('modification_date', [], 'platform'),
            ],
        ];
    }

    protected function getHiddenFilters(?array $options = [], ?array $extra = []): array
    {
        $hiddenFilters = parent::getHiddenFilters($options, $extra);
        // only get non deleted resources
        $hiddenFilters['active'] = true;

        return $hiddenFilters;
    }
}
