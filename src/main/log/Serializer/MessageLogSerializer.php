<?php

namespace Claroline\LogBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\LogBundle\Entity\MessageLog;

class MessageLogSerializer extends AbstractLogSerializer
{
    public function getClass(): string
    {
        return MessageLog::class;
    }

    public function serialize(MessageLog $messageLog, array $options): array
    {
        $serialized = $this->serializeCommon($messageLog, $options);
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return $serialized;
        }

        $receiver = null;
        if ($messageLog->getReceiver()) {
            $receiver = $this->userSerializer->serialize($messageLog->getReceiver(), [SerializerInterface::SERIALIZE_MINIMAL]);
        }

        return array_merge($serialized, [
            'receiver' => $receiver,
        ]);
    }
}
