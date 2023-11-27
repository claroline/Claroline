<?php

namespace Claroline\LogBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\LogBundle\Entity\OperationalLog;

class OperationalLogSerializer extends AbstractLogSerializer
{
    public function getClass(): string
    {
        return OperationalLog::class;
    }

    public function serialize(OperationalLog $operationalLog, array $options = []): array
    {
        $serialized = $this->serializeCommon($operationalLog, $options);
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return $serialized;
        }

        return array_merge($serialized, [
            'objectClass' => $operationalLog->getObjectClass(),
            'objectId' => $operationalLog->getObjectId(),
            'changeset' => $operationalLog->getChangeset(),
        ]);
    }
}
