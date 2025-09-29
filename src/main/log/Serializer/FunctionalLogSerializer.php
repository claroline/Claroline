<?php

namespace Claroline\LogBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\LogBundle\Entity\FunctionalLog;

class FunctionalLogSerializer extends AbstractLogSerializer
{
    public function getClass(): string
    {
        return FunctionalLog::class;
    }

    public function serialize(FunctionalLog $functionalLog, array $options = []): array
    {
        $serialized = $this->serializeCommon($functionalLog, $options);
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return $serialized;
        }

        return array_merge($serialized, [
            'contextId' => $functionalLog->getContextId(),
            'objectClass' => $functionalLog->getObjectClass(),
            'objectId' => $functionalLog->getObjectId(),
        ]);
    }
}
