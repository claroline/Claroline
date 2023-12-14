<?php

namespace Claroline\LogBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\LogBundle\Entity\SecurityLog;

class SecurityLogSerializer extends AbstractLogSerializer
{
    public function getClass(): string
    {
        return SecurityLog::class;
    }

    public function serialize(SecurityLog $securityLog, array $options = []): array
    {
        $serialized = $this->serializeCommon($securityLog, $options);
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return $serialized;
        }

        $target = null;
        if ($securityLog->getTarget()) {
            $target = $this->userSerializer->serialize($securityLog->getTarget(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return array_merge($serialized, [
            'target' => $target,
        ]);
    }
}
