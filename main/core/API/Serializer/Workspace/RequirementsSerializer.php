<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\Resource\ResourceNodeSerializer;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Workspace\Requirements;

class RequirementsSerializer
{
    /** @var ResourceNodeSerializer */
    private $resourceNodeSerializer;
    /** @var RoleSerializer */
    private $roleSerializer;
    /** @var UserSerializer */
    private $userSerializer;

    /**
     * RequirementsSerializer constructor.
     *
     * @param ResourceNodeSerializer $resourceNodeSerializer
     * @param RoleSerializer         $roleSerializer
     * @param UserSerializer         $userSerializer
     */
    public function __construct(
        ResourceNodeSerializer $resourceNodeSerializer,
        RoleSerializer $roleSerializer,
        UserSerializer $userSerializer
    ) {
        $this->resourceNodeSerializer = $resourceNodeSerializer;
        $this->roleSerializer = $roleSerializer;
        $this->userSerializer = $userSerializer;
    }

    /**
     * Serializes an Requirements entity for the JSON api.
     *
     * @param Requirements $requirements
     * @param array        $options
     *
     * @return array - the serialized representation of the workspace requirements
     */
    public function serialize(Requirements $requirements, array $options = [])
    {
        $serialized = [
            'id' => $requirements->getUuid(),
            'user' => $requirements->getUser() ?
                $this->userSerializer->serialize($requirements->getUser(), [Options::SERIALIZE_MINIMAL]) :
                null,
            'role' => $requirements->getRole() ?
                $this->roleSerializer->serialize($requirements->getRole(), [Options::SERIALIZE_MINIMAL]) :
                null,
        ];
        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized['resources'] = array_map(function (ResourceNode $resourceNode) {
                return $this->resourceNodeSerializer->serialize($resourceNode, [Options::SERIALIZE_MINIMAL]);
            }, $requirements->getResources()->toArray());
        }

        return $serialized;
    }
}
