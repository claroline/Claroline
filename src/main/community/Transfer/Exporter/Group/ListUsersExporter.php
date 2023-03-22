<?php

namespace Claroline\CommunityBundle\Transfer\Exporter\Group;

use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListUsersExporter extends AbstractListExporter
{
    public static function getAction(): array
    {
        return ['group', 'list_users'];
    }

    protected static function getClass(): string
    {
        return User::class;
    }

    public function execute(int $batchNumber, ?array $options = [], ?array $extra = []): array
    {
        if (empty($extra['group'])) {
            // avoid exposing the full users list if no group is selected
            return [];
        }

        $data = [];

        $users = parent::execute($batchNumber, $options, $extra);
        foreach ($users as $user) {
            $data[] = [
                'user' => $user,
                'group' => $extra['group'],
            ];
        }

        return $data;
    }

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array
    {
        $extraDef = parent::getExtraDefinition($options, $extra);
        $extraDef['fields'][] = [
            'name' => 'group',
            'label' => $this->translator->trans('group', [], 'platform'),
            'type' => 'group',
        ];

        return $extraDef;
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [
                // User info
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
                ], [
                    'name' => 'user.administrativeCode',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user administrativeCode', [], 'schema'),
                ], [
                    'name' => 'user.meta.description',
                    'type' => 'string',
                    'description' => $this->translator->trans('The user description', [], 'schema'),
                ], [
                    'name' => 'user.meta.created',
                    'type' => 'date',
                    'description' => $this->translator->trans('The user creation date', [], 'schema'),
                ], [
                    'name' => 'user.meta.lastActivity',
                    'type' => 'date',
                    'description' => $this->translator->trans('The user last activity date', [], 'schema'),
                ], [
                    'name' => 'user.restrictions.disabled',
                    'type' => 'boolean',
                    'description' => $this->translator->trans('Is the user disabled ?', [], 'schema'),
                ],
                // Group info
                [
                    'name' => 'group.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group id', [], 'schema'),
                ], [
                    'name' => 'group.name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group name', [], 'schema'),
                ],
            ],
        ];
    }

    protected function getHiddenFilters(?array $options = [], ?array $extra = []): array
    {
        if (!empty($extra['group'])) {
            return [
                'group' => $extra['group']['id'],
            ];
        }

        return [];
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
