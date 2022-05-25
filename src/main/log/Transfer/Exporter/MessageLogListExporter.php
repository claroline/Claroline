<?php

namespace Claroline\LogBundle\Transfer\Exporter;

use Claroline\LogBundle\Entity\MessageLog;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class MessageLogListExporter extends AbstractListExporter
{
    public function getAction(): array
    {
        return ['log', 'message_log_list'];
    }

    protected static function getClass(): string
    {
        return MessageLog::class;
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

                // sender
                [
                    'name' => 'sender.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user id', [], 'schema'),
                ], [
                    'name' => 'sender.email',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user email address', [], 'schema'),
                ], [
                    'name' => 'sender.username',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user username', [], 'schema'),
                ], [
                    'name' => 'sender.firstName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user first name', [], 'schema'),
                ], [
                    'name' => 'sender.lastName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user last name', [], 'schema'),
                ],

                // receiver
                [
                    'name' => 'receiver.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user id', [], 'schema'),
                ], [
                    'name' => 'receiver.email',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user email address', [], 'schema'),
                ], [
                    'name' => 'receiver.username',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user username', [], 'schema'),
                ], [
                    'name' => 'receiver.firstName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user first name', [], 'schema'),
                ], [
                    'name' => 'receiver.lastName',
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
                'name' => 'sender',
                'label' => $this->translator->trans('sender', [], 'platform'),
                'type' => 'user',
            ], [
                'name' => 'receiver',
                'label' => $this->translator->trans('receiver', [], 'platform'),
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
