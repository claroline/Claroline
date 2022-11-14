<?php

namespace Claroline\CoreBundle\API\Serializer\Workspace;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CoreBundle\Entity\Workspace\Shortcuts;

class ShortcutsSerializer
{
    use SerializerTrait;

    /** @var RoleSerializer */
    private $roleSerializer;

    /**
     * ShortcutsSerializer constructor.
     */
    public function __construct(RoleSerializer $roleSerializer)
    {
        $this->roleSerializer = $roleSerializer;
    }

    public function getName()
    {
        return 'workspace_shortcut';
    }

    /**
     * Serializes a Workspace Shortcuts entity for the JSON api.
     *
     * @return array
     */
    public function serialize(Shortcuts $shortcuts, array $options = [])
    {
        return [
            'role' => $this->roleSerializer->serialize($shortcuts->getRole(), [Options::SERIALIZE_MINIMAL]),
            'data' => $shortcuts->getData(),
        ];
    }
}
