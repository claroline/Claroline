<?php

namespace Claroline\CoreBundle\Transfer\Importer\Directory;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\TransferBundle\Transfer\Importer\AbstractImporter;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @todo merge some logic with CreateOrUpdate action.
 */
class Create extends AbstractImporter
{
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        Crud $crud,
        ObjectManager $om,
        SerializerProvider $serializer,
        TranslatorInterface $translator
    ) {
        $this->crud = $crud;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->translator = $translator;
    }

    public function execute(array $data): array
    {
        // todo find a generic way to find the identifiers
        /** @var Workspace $workspace */
        $workspace = $this->om->getObject($data['workspace'], Workspace::class, ['code']);
        if (!$workspace) {
            throw new \Exception('Workspace '.json_encode($data['workspace'])." doesn't exists.");
        }

        // get the parent of the new dirs
        if (isset($data['directory'])) {
            /** @var ResourceNode $parent */
            $parent = $this->om->getRepository(ResourceNode::class)->findOneBy(['uuid' => $data['directory']['id']]);
        } else {
            /** @var ResourceNode $parent */
            $parent = $this->om->getRepository(ResourceNode::class)->findOneBy(['workspace' => $workspace, 'parent' => null]);
        }

        $roles = [];
        if (isset($data['user'])) {
            /** @var User $user */
            $user = $this->om->getRepository(User::class)->findOneBy(['username' => $data['user']]);

            if ($user) {
                foreach ($user->getEntityRoles() as $role) {
                    if (Role::USER_ROLE === $role->getType()) {
                        $roles[$role->getUuid()] = $role;
                    }
                }
            }
        }

        if (isset($data['roles'])) {
            foreach ($data['roles'] as $role) {
                $roleKeys = array_keys($role);
                if (in_array('translationKey', $roleKeys)) {
                    /** @var Role $object */
                    $object = $this->om->getRepository(Role::class)->findOneBy([
                        'translationKey' => $role['translationKey'],
                        'workspace' => $workspace,
                    ]);

                    unset($roleKeys[array_search('translationKey', $roleKeys)]);
                }

                if (empty($object)) {
                    /** @var Role $object */
                    $object = $this->om->getObject($role, Role::class, $roleKeys);
                }

                if (!$object) {
                    throw new \Exception('Role '.implode(',', $role).' does not exists');
                }

                $roles[$object->getUuid()] = $object;
            }
        }

        if (empty($roles) && !empty($workspace->getDefaultRole())) {
            $roles[$workspace->getDefaultRole()->getUuid()] = $workspace->getDefaultRole();
        }

        $permissions = [
            'open' => isset($data['open']) ? $data['open'] : false,
            'edit' => isset($data['edit']) ? $data['edit'] : false,
            'delete' => isset($data['delete']) ? $data['delete'] : false,
            'administrate' => isset($data['administrate']) ? $data['administrate'] : false,
            'export' => isset($data['export']) ? $data['export'] : false,
            'copy' => isset($data['copy']) ? $data['copy'] : false,
        ];
        if (isset($data['create'])) {
            $create = explode(',', $data['create']);
            $create = array_map(function ($type) {
                return trim($type);
            }, $create);

            $permissions['create'] = $create;
        }

        $rights = [];
        foreach ($roles as $role) {
            $rights[] = [
                'permissions' => $permissions,
                'name' => $role->getName(),
                'translationKey' => $role->getTranslationKey(),
            ];
        }

        $dataResourceNode = [
            'name' => $data['name'],
            'meta' => [
                'published' => true,
                'type' => 'directory',
            ],
            'rights' => $rights,
        ];

        if (!empty($data['code'])) {
            $dataResourceNode['code'] = $data['code'];
        }

        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->crud->create(ResourceNode::class, $dataResourceNode);

        $resourceNode->setParent($parent);
        $resourceNode->setWorkspace($parent->getWorkspace());

        $resource = $this->crud->create(Directory::class, []);
        $resource->setResourceNode($resourceNode);

        $this->om->persist($resourceNode);
        $this->om->persist($resource);

        return [];
    }

    public function getSchema(?array $options = [], ?array $extra = []): array
    {
        $types = array_map(function (ResourceType $type) {
            return $type->getName();
        }, $this->om->getRepository(ResourceType::class)->findAll());
        $types = implode(', ', $types);

        $directory = [
            '$schema' => 'http:\/\/json-schema.org\/draft-04\/schema#',
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'description' => $this->translator->trans('directory_name', [], 'transfer'),
                ],
                'code' => [
                    'type' => 'string',
                    'description' => $this->translator->trans('directory_code', [], 'transfer'),
                ],
                'open' => [
                    'type' => 'boolean',
                    'description' => $this->translator->trans('directory_open', [], 'transfer'),
                ],
                'delete' => [
                    'type' => 'boolean',
                    'description' => $this->translator->trans('directory_delete', [], 'transfer'),
                ],
                'edit' => [
                    'type' => 'boolean',
                    'description' => $this->translator->trans('directory_edit', [], 'transfer'),
                ],
                'copy' => [
                    'type' => 'boolean',
                    'description' => $this->translator->trans('directory_copy', [], 'transfer'),
                ],
                'export' => [
                    'type' => 'boolean',
                    'description' => $this->translator->trans('directory_export', [], 'transfer'),
                ],
                'administrate' => [
                    'type' => 'boolean',
                    'description' => $this->translator->trans('directory_administrate', [], 'transfer'),
                ],
                'create' => [
                    'type' => 'string',
                    'description' => $this->translator->trans('directory_creation', ['%types%' => $types], 'transfer'),
                ],
                'user' => [
                    'type' => 'string',
                    'description' => $this->translator->trans('directory_user', [], 'transfer'),
                ],
                'roles' => [
                    'type' => 'array',
                    'uniqueItems' => true,
                    'items' => [
                        'oneOf' => [
                            [
                                'type' => 'object',
                                'properties' => [
                                    'id' => [
                                        'type' => 'string',
                                        'description' => 'The role id',
                                    ],
                                    'name' => [
                                        'type' => 'string',
                                        'description' => 'The role name',
                                    ],
                                    'translationKey' => [
                                        'type' => 'string',
                                        'description' => 'The role displayed value',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],

            // this kind of hacky because this is not the true permissions description to begin with
            // if you remove this section it will not show because it'll go through the explainIdentifiers method (not $root in schema)
            'claroline' => [
                'requiredAtCreation' => ['name', 'code'],
                'class' => Directory::class,
            ],
        ];

        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            $directory['properties']['workspace'] = [
                'type' => 'string',
                'description' => 'The workspace code',
            ];
            $directory['claroline']['requiredAtCreation'][] = 'workspace';
        }

        return [
            '$root' => json_decode(json_encode($directory)),
        ];
    }

    public function getExtraDefinition(?array $options = [], ?array $extra = []): array
    {
        $root = $this->serializer->serialize($this->om->getRepository(ResourceNode::class)->findOneBy(['parent' => null, 'workspace' => $extra['workspace']['id']]));

        return ['fields' => [
            [
                'name' => 'directory',
                'type' => 'resource',
                'required' => true,
                'label' => $this->translator->trans('parent', [], 'platform'),
                'options' => [
                    'picker' => [
                        'filters' => [
                            ['property' => 'workspace', 'value' => $extra['workspace']['id'], 'locked' => true],
                            ['property' => 'resourceType', 'value' => 'directory', 'locked' => true],
                        ],
                        'current' => $root,
                        'root' => $root,
                    ],
                ],
            ],
        ]];
    }

    public function supports(string $format, ?array $options = [], ?array $extra = []): bool
    {
        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            return false;
        }

        return in_array($format, ['json', 'csv']);
    }

    public static function getAction(): array
    {
        return ['directory', 'create'];
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }
}
