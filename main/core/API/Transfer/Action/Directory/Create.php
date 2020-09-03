<?php

namespace Claroline\CoreBundle\API\Transfer\Action\Directory;

use Claroline\AppBundle\API\Crud;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\API\Transfer\Action\AbstractAction;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\Directory;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\Translation\TranslatorInterface;

class Create extends AbstractAction
{
    /** @var Crud */
    private $crud;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var TranslatorInterface */
    private $translator;

    /**
     * CreateOrUpdate constructor.
     *
     * @param Crud                $crud
     * @param ObjectManager       $om
     * @param SerializerProvider  $serializer
     * @param TranslatorInterface $translator
     */
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

    /**
     * @param array $data
     * @param array $successData
     *
     * @throws \Exception
     */
    public function execute(array $data, &$successData = [])
    {
        //todo find a generic way to find the identifiers
        /** @var Workspace $workspace */
        $workspace = $this->om->getObject($data['workspace'], Workspace::class, ['code']);
        /** @var ResourceNode $parent */
        $parent = $this->om->getRepository(ResourceNode::class)->findOneBy(['workspace' => $workspace, 'parent' => null]);

        if (!$workspace) {
            throw new \Exception('Workspace '.json_encode($data['workspace'])." doesn't exists.");
        }

        $permissions = [
            'open' => isset($data['open']) ? $data['open'] : false,
            'edit' => isset($data['edit']) ? $data['edit'] : false,
            'delete' => isset($data['delete']) ? $data['delete'] : false,
            'administrate' => isset($data['administrate']) ? $data['administrate'] : false,
            'export' => isset($data['export']) ? $data['export'] : false,
            'copy' => isset($data['copy']) ? $data['copy'] : false,
        ];

        $roles = [];
        if (isset($data['user'])) {
            /** @var User $user */
            $user = $this->om->getRepository(User::class)->findOneBy(['username' => $data['user']]);

            foreach ($user->getEntityRoles() as $role) {
                if (Role::USER_ROLE === $role->getType()) {
                    $roles[] = $role;
                }
            }
        }

        if (isset($data['roles'])) {
            foreach ($data['roles'] as $role) {
                $object = $this->om->getObject($role, Role::class, array_keys($role));

                if (!$object) {
                    throw new \Exception('Role '.implode(',', $role).' does not exists');
                }

                $roles[] = $object;
            }
        }

        if (empty($roles)) {
            $roles[] = $workspace->getDefaultRole();
        }

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

        if (isset($data['directory'])) {
            /** @var ResourceNode $parent */
            $parent = $this->om->getRepository(ResourceNode::class)->findOneBy(['uuid' => $data['directory']['id']]);
        }

        /** @var ResourceNode $resourceNode */
        $resourceNode = $this->crud->create(ResourceNode::class, $dataResourceNode);

        $resourceNode->setParent($parent);
        $resourceNode->setWorkspace($parent->getWorkspace());

        $resource = $this->crud->create(Directory::class, []);
        $resource->setResourceNode($resourceNode);

        $this->om->persist($resourceNode);
        $this->om->persist($resource);
    }

    /**
     * @param array $options
     * @param array $extra
     *
     * @return array
     */
    public function getSchema(array $options = [], array $extra = [])
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

            //this kind of hacky because this is not the true permissions description to begin with
            //if you remove this section it will not show because it'll go through the explainIdentifiers method (not $root in schema)
            'claroline' => [
                'requiredAtCreation' => ['name'],
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

        $schema = [
            '$root' => json_decode(json_encode($directory)),
        ];

        return $schema;
    }

    public function getExtraDefinition(array $options = [], array $extra = [])
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

    public function supports($format, array $options = [], array $extra = [])
    {
        if (!in_array(Options::WORKSPACE_IMPORT, $options)) {
            return false;
        }

        return in_array($format, ['json', 'csv']);
    }

    /**
     * @return array
     */
    public function getAction()
    {
        return ['directory', 'create'];
    }

    public function getBatchSize()
    {
        return 100;
    }

    public function getMode()
    {
        return self::MODE_CREATE;
    }

    public function clear(ObjectManager $om)
    {
    }
}
