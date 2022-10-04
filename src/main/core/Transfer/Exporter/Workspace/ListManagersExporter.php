<?php

namespace Claroline\CoreBundle\Transfer\Exporter\Workspace;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

/**
 * Lists all users with a workspace manager role.
 */
class ListManagersExporter extends AbstractListExporter
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public static function getAction(): array
    {
        return ['workspace', 'list_managers'];
    }

    protected static function getClass(): string
    {
        return User::class;
    }

    public function execute(int $batchNumber, ?array $options = [], ?array $extra = []): array
    {
        $users = parent::execute($batchNumber, $options, $extra);

        $data = [];
        foreach ($users as $user) {
            if (!empty($extra['workspace'])) {
                $data[] = [
                    'user' => $user,
                    'workspace' => $extra['workspace'],
                ];
            } else {
                // find all workspaces the user manages
                $workspaces = $this->om->getRepository(Workspace::class)->findManaged($user['id']);
                foreach ($workspaces as $workspace) {
                    $data[] = [
                        'user' => $user,
                        'workspace' => $this->serializer->serialize($workspace, [SerializerInterface::SERIALIZE_TRANSFER]),
                    ];
                }
            }
        }

        return $data;
    }

    protected function getHiddenFilters(?array $options = [], ?array $extra = []): array
    {
        return [
            'workspaceManager' => true,
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

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        return [
            'properties' => [
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
                ], [
                    'name' => 'workspace.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace id', [], 'schema'),
                ], [
                    'name' => 'workspace.name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace name', [], 'schema'),
                ], [
                    'name' => 'workspace.code',
                    'type' => 'string',
                    'description' => $this->translator->trans('The workspace code', [], 'schema'),
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
