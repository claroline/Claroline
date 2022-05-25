<?php

namespace Claroline\CoreBundle\Transfer\Exporter\Workspace;

use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['workspace', 'list'];
    }

    protected static function getClass(): string
    {
        return Workspace::class;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace id or uuid', [], 'schema'),
                ], [
                    'name' => 'name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace name', [], 'schema'),
                ], [
                    'name' => 'code',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace code', [], 'schema'),
                ], [
                    'name' => 'slug',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace slug', [], 'schema'),
                ], [
                    'name' => 'meta.personal',
                    'type' => 'boolean',
                    'description' => $this->translator->trans('The workspace is a personal workspace', [], 'schema'),
                ], [
                    'name' => 'meta.model',
                    'type' => 'boolean',
                    'description' => $this->translator->trans('The workspace is a model', [], 'schema'),
                ], [
                    'name' => 'meta.archived',
                    'type' => 'boolean',
                    'description' => $this->translator->trans('The workspace is archived', [], 'schema'),
                ], [
                    'name' => 'meta.description',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace description', [], 'schema'),
                ], [
                    'name' => 'meta.created',
                    'type' => 'date',
                    'description' => $this->translator->trans('The workspace creation date', [], 'schema'),
                ], [
                    'name' => 'meta.updated',
                    'type' => 'date',
                    'description' => $this->translator->trans('The workspace last modification date', [], 'schema'),
                ],
            ],
        ];
    }

    protected function getAvailableFilters(): array
    {
        return [
            [
                'name' => 'archived',
                'label' => $this->translator->trans('archived', [], 'platform'),
                'type' => 'boolean',
            ], [
                'name' => 'model',
                'label' => $this->translator->trans('model', [], 'platform'),
                'type' => 'boolean',
            ], [
                'name' => 'personal',
                'label' => $this->translator->trans('personal_workspace', [], 'platform'),
                'type' => 'boolean',
            ], [
                'name' => 'hidden',
                'label' => $this->translator->trans('hidden', [], 'platform'),
                'type' => 'boolean',
            ], [
                'name' => 'selfRegistration',
                'label' => $this->translator->trans('public_registration', [], 'platform'),
                'type' => 'boolean',
            ], [
                'name' => 'tags',
                'label' => $this->translator->trans('tags', [], 'platform'),
                'type' => 'tag',
                'options' => [
                    'objectClass' => Workspace::class,
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
                'name' => 'createdAt',
                'label' => $this->translator->trans('creation_date', [], 'platform'),
            ], [
                'name' => 'updatedAt',
                'label' => $this->translator->trans('modification_date', [], 'platform'),
            ],
        ];
    }
}
