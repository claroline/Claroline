<?php

namespace Claroline\CoreBundle\API\Serializer\Log;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\Log\MessageLog;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class MessageLogSerializer
{
    use SerializerTrait;

    private $userSerializer;

    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    public function serialize(MessageLog $messageLog): array
    {
        $sender = null;
        if ($messageLog->getSender()) {
            $sender = $this->userSerializer->serialize($messageLog->getSender(), [Options::SERIALIZE_MINIMAL]);
        }

        $receiver = null;
        if ($messageLog->getReceiver()) {
            $receiver = $this->userSerializer->serialize($messageLog->getReceiver(), [Options::SERIALIZE_MINIMAL]);
        }

        return [
            'date' => DateNormalizer::normalize($messageLog->getDate()),
            'details' => $messageLog->getDetails(),
            'sender' => $sender,
            'event' => $messageLog->getEvent(),
            'receiver' => $receiver,
        ];
    }
}
