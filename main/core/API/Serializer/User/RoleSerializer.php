<?php

namespace Claroline\CoreBundle\API\Serializer\User;

use Claroline\CoreBundle\API\Options;
use Claroline\CoreBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\Role;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.role")
 * @DI\Tag("claroline.serializer")
 */
class RoleSerializer
{
    /** @var SerializerProvider */
    private $serializer;

    /**
     * RoleSerializer constructor.
     *
     * @DI\InjectParams({
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $serializer)
    {
        $this->serializer = $serializer;
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
            $serialized['restrictions'] = $this->serializeRestrictions($role);

            if ($workspace = $role->getWorkspace()) {
                $serialized['workspace'] = $this->serializer->serialize($workspace, [Options::SERIALIZE_MINIMAL]);
            }
        }

        return $serialized;
    }

    public function serializeMeta(Role $role, array $options = [])
    {
        return [
           'readOnly' => $role->isReadOnly(),
           'type' => $role->getType(),
           'personalWorkspaceCreationEnabled' => $role->getPersonalWorkspaceCreationEnabled(),
       ];
    }

    public function serializeRestrictions(Role $role, array $options = [])
    {
        return [
            'maxUsers' => $role->getMaxUsers(),
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
    public function deserialize($data, Role $role = null, array $options = [])
    {
        if (isset($data['translationKey'])) {
            $role->setTranslationKey($data['translationKey']);
            //2 roles can have the same translationKey while the name is unique, for now we only allow to create
            //platform roles so it's not an issue but it's going to need improvements
            //when workspaces and custom roles will be supported
            $role->setName('ROLE_'.str_replace(' ', '_', strtoupper($data['translationKey'])));
        }

        return $role;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\Role';
    }
}
