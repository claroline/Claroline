<?php

namespace Claroline\CommunityBundle\Transfer\Exporter\User;

use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListExporter extends AbstractListExporter
{
    public static function getAction(): array
    {
        return ['user', 'list'];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        return in_array($format, ['json', 'csv']);
    }

    protected static function getClass(): string
    {
        return User::class;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [
                [
                    'name' => 'id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user id', [], 'schema'),
                ], [
                    'name' => 'email',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user email address', [], 'schema'),
                ], [
                    'name' => 'username',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user username', [], 'schema'),
                ], [
                    'name' => 'firstName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user first name', [], 'schema'),
                ], [
                    'name' => 'lastName',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user last name', [], 'schema'),
                ], [
                    'name' => 'administrativeCode',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user administrativeCode', [], 'schema'),
                ], [
                    'name' => 'meta.description',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user description', [], 'schema'),
                ], [
                    'name' => 'meta.created',
                    'type' => 'date',
                    'description' => $this->translator->trans('The user creation date', [], 'schema'),
                ], [
                    'name' => 'meta.lastActivity',
                    'type' => 'date',
                    'description' => $this->translator->trans('The user last activity date', [], 'schema'),
                ], [
                    'name' => 'restrictions.disabled',
                    'type' => 'boolean',
                    'description' => $this->translator->trans('Is the user disabled ?', [], 'schema'),
                ], [
                    'name' => 'restrictions.dates',
                    'type' => 'date-range',
                    'description' => $this->translator->trans('The user restrictions dates', [], 'schema'),
                ],
            ],
        ];
    }

    protected function getAvailableFilters(): array
    {
        return [
            [
                'name' => 'email',
                'type' => 'string',
                'label' => $this->translator->trans('email', [], 'platform'),
            ], [
                'name' => 'username',
                'type' => 'string',
                'label' => $this->translator->trans('username', [], 'platform'),
            ], [
                'name' => 'restrictions.disabled',
                'type' => 'boolean',
                'label' => $this->translator->trans('disabled', [], 'platform'),
                'alias' => 'isDisabled',
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
                'name' => 'email',
                'label' => $this->translator->trans('email', [], 'platform'),
            ], [
                'name' => 'username',
                'label' => $this->translator->trans('username', [], 'platform'),
            ], [
                'name' => 'firstName',
                'label' => $this->translator->trans('first_name', [], 'platform'),
            ], [
                'name' => 'lastName',
                'label' => $this->translator->trans('last_name', [], 'platform'),
            ],
        ];
    }
}
