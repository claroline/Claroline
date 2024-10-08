<?php

namespace Claroline\CommunityBundle\Transfer\Importer\User;

use Claroline\AppBundle\API\Crud;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;

final class SetMainOrganization extends AbstractImporter
{
    public function __construct(
        private readonly Crud $crud
    ) {
    }

    public function execute(array $data): array
    {
        /** @var User $user */
        $user = $this->crud->find(User::class, $data[static::getAction()[0]]);

        /** @var Organization $organization */
        $organization = $this->crud->find(Organization::class, $data['organization']);

        if (!empty($user) && !empty($organization)) {
            $this->crud->update($user, [
                'id' => $user->getUuid(),
                'mainOrganization' => [
                    'id' => $organization->getUuid(),
                ],
            ]);

            return [
                'set_main_organization' => [[
                    'data' => $data,
                    'log' => sprintf('%s added to organization %s.', static::getAction()[0], $organization->getName()),
                ]],
            ];
        }

        return [];
    }

    public static function getAction(): array
    {
        return ['user', 'set_main_organization'];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'user' => User::class,
            'organization' => Organization::class,
        ];
    }
}
