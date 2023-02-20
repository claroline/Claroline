<?php

namespace Claroline\CoreBundle\Transfer\Exporter\Workspace;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Exporter\AbstractListExporter;

class ListGroupsExporter extends AbstractListExporter
{
    /** @var ObjectManager */
    private $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public static function getAction(): array
    {
        return ['workspace', 'list_groups'];
    }

    protected static function getClass(): string
    {
        return Group::class;
    }

    public function execute(int $batchNumber, ?array $options = [], ?array $extra = []): array
    {
        $groups = parent::execute($batchNumber, $options, $extra);

        $data = [];
        foreach ($groups as $group) {
            if (!empty($extra['workspace'])) {
                $data[] = [
                    'group' => $group,
                    'workspace' => $extra['workspace'],
                ];
            } else {
                // find all workspaces the group is registered to
                $workspaces = [];
                if (!empty($group['roles'])) {
                    $roleNames = array_map(function (array $roleData) {
                        return $roleData['name'];
                    }, $group['roles']);

                    $workspaces = $this->om->getRepository(Workspace::class)->findByRoles($roleNames);
                }

                foreach ($workspaces as $workspace) {
                    $data[] = [
                        'group' => $group,
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
                    'name' => 'group.id',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group id', [], 'schema'),
                ], [
                    'name' => 'group.name',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group name', [], 'schema'),
                ], [
                    'name' => 'group.meta.description',
                    'type' => 'string',
                    'description' => $this->translator->trans('The group description', [], 'schema'),
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
                'name' => 'name',
                'type' => 'string',
                'label' => $this->translator->trans('name', [], 'platform'),
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
}
