<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\CoreBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.role")
 * @DI\Tag("claroline.serializer")
 */
class RoleSerializer
{
    use SerializerTrait;

    /** @var SerializerProvider */
    private $serializer;

    /** @var ObjectManager */
    private $om;

    /**
     * RoleSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param SerializerProvider $serializer
     * @param ObjectManager      $om
     */
    public function __construct(SerializerProvider $serializer, ObjectManager $om)
    {
        $this->serializer = $serializer;
        $this->om = $om;
    }

    /**
     * Serializes a Role entity.
     *
     * @param Role  $role
     * @param array $options
     *
     * @return array
     */
    public function serialize(Role $role, array $options = [])
    {
        $serialized = [
            'id' => $role->getUuid(),
            'translationKey' => $role->getTranslationKey(),
            'name' => $role->getName(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized['meta'] = $this->serializeMeta($role, $options);
            $serialized['restrictions'] = $this->serializeRestrictions($role, $options);

            if ($workspace = $role->getWorkspace()) {
                $serialized['workspace'] = $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
            }
        }

        return $serialized;
    }

    /**
     * Serialize role metadata.
     *
     * @param Role  $role
     * @param array $options
     *
     * return array
     */
    public function serializeMeta(Role $role, array $options = [])
    {
        $meta = [
           'readOnly' => $role->isReadOnly(),
           'type' => $role->getType(),
           'personalWorkspaceCreationEnabled' => $role->getPersonalWorkspaceCreationEnabled(),
       ];

        if (in_array(Options::SERIALIZE_COUNT_USER, $options) && $role->getType() !== Role::USER_ROLE) {
            $meta['users'] = $this->om->getRepository('ClarolineCoreBundle:User')->countUsersByRoleIncludingGroup($role);
        }

        if ($role->getType() === Role::USER_ROLE) {
            $meta['users'] = 1;
            $meta['user'] = $this->serializer->serialize($role->getUsers()->toArray()[0], [Options::SERIALIZE_MINIMAL]);
        }

        return $meta;
    }

    /**
     * Serialize role restrictions.
     *
     * @param Role  $role
     * @param array $options
     *
     * return array
     */
    public function serializeRestrictions(Role $role, array $options = [])
    {
        $adminTools = [];
        //easier request than count users wich will go into mysql cache so I'm not too woried about looping here.
        foreach ($this->om->getRepository('ClarolineCoreBundle:Tool\AdminTool')->findAll() as $adminTool) {
            $adminTools[$adminTool->getName()] = $role->getAdminTools()->contains($adminTool);
        }

        return [
            'maxUsers' => $role->getMaxUsers(),
            'adminTools' => $adminTools,
        ];
    }

    /**
     * Deserializes data into a Role entity.
     *
     * @param \stdClass $data
     * @param Role      $role
     * @param array     $options
     *
     * @return Role
     */
    public function deserialize($data, Role $role, array $options = [])
    {
        if (isset($data['translationKey'])) {
            $role->setTranslationKey($data['translationKey']);
            //2 roles can have the same translationKey while the name is unique, for now we only allow to create
            //platform roles so it's not an issue but it's going to need improvements
            //when workspaces and custom roles will be supported
            $role->setName('ROLE_'.str_replace(' ', '_', strtoupper($data['translationKey'])));
        }

        $this->sipe('meta.personalWorkspaceCreationEnabled', 'setPersonalWorkspaceCreationEnabled', $data, $role);

        return $role;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Role';
    }
}
