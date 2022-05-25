<?php

namespace Claroline\LogBundle\Transfer\Exporter;

use Claroline\LogBundle\Entity\SecurityLog;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class SecurityLogListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['log', 'security_log_list'];
    }

    protected static function getClass(): string
    {
        return SecurityLog::class;
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
                ], [
                    'name' => 'doer_ip',
                    'type' => 'string',
                    'description' => $this->translator->trans('The address IP of the user', [], 'schema'),
                ],

                // sender
                [
                    'name' => 'doer.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user id', [], 'schema'),
                ], [
                    'name' => 'doer.email',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user email address', [], 'schema'),
                ], [
                    'name' => 'doer.username',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user username', [], 'schema'),
                ], [
                    'name' => 'doer.firstName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user first name', [], 'schema'),
                ], [
                    'name' => 'doer.lastName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user last name', [], 'schema'),
                ],

                // target
                [
                    'name' => 'target.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user id', [], 'schema'),
                ], [
                    'name' => 'target.email',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user email address', [], 'schema'),
                ], [
                    'name' => 'target.username',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user username', [], 'schema'),
                ], [
                    'name' => 'target.firstName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user first name', [], 'schema'),
                ], [
                    'name' => 'target.lastName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user last name', [], 'schema'),
                ],
            ],
        ];
    }

    protected function getAvailableFilters(): array
    {
        return [
            [
                'name' => 'doer',
                'label' => $this->translator->trans('user', [], 'platform'),
                'type' => 'user',
            ], [
                'name' => 'target',
                'label' => $this->translator->trans('target', [], 'platform'),
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
