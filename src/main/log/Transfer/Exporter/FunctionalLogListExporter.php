<?php

namespace Claroline\LogBundle\Transfer\Exporter;

use Claroline\AppBundle\API\Options;
use Claroline\LogBundle\Entity\FunctionalLog;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class FunctionalLogListExporter extends AbstractListExporter
{
    public static function getAction(): array
    {
        return ['log', 'functional_log_list'];
    }

    protected static function getClass(): string
    {
        return FunctionalLog::class;
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

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [
                [
                    'name' => 'date',
                    'type' => 'date',
                    'description' => $this->translator->trans('The log date', [], 'schema'),
                ], [
                    'name' => 'event',
                    'type' => 'string',
                    'description' => $this->translator->trans('The log action', [], 'schema'),
                ], [
                    'name' => 'details',
                    'type' => 'string',
                    'description' => $this->translator->trans('The log details', [], 'schema'),
                ],

                // user
                [
                    'name' => 'user.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user id', [], 'schema'),
                ], [
                    'name' => 'user.email',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user email address', [], 'schema'),
                ], [
                    'name' => 'user.username',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user username', [], 'schema'),
                ], [
                    'name' => 'user.firstName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user first name', [], 'schema'),
                ], [
                    'name' => 'user.lastName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user last name', [], 'schema'),
                ],

                // workspace
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
                ], [
                    'name' => 'workspace.slug',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace slug', [], 'schema'),
                ],

                // resource
                [
                    'name' => 'resource.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The resource id or uuid', [], 'schema'),
                ], [
                    'name' => 'resource.name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The resource name', [], 'schema'),
                ], [
                    'name' => 'resource.slug',
                    'type' => 'string',
                    'description' => $this->translator->trans('The resource slug', [], 'schema'),
                ],
            ],
        ];
    }

    protected function getAvailableFilters(): array
    {
        return [
            [
                'name' => 'resource',
                'label' => $this->translator->trans('resource', [], 'platform'),
                'type' => 'resource',
            ], [
                'name' => 'user',
                'label' => $this->translator->trans('user', [], 'platform'),
                'type' => 'user',
            ],
        ];
    }

    protected function getAvailableSortBy(): array
    {
        return [
            [
                'name' => 'date',
                'label' => $this->translator->trans('date', [], 'platform'),
            ],
        ];
    }
}
