<?php

namespace Claroline\CoreBundle\API\Serializer\Resource;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;

abstract class AbstractResourceSerializer
{
    /**
     * Should we include the minimal representation of the node in the
     * AbstractResource serialized structure ?
     */
    const SERIALIZE_NODE = 'SERIALIZE_NODE';

    public function getClass(): string
    {
        return AbstractResource::class;
    }
}
