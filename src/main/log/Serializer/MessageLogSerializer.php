<?php

namespace Claroline\LogBundle\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\LogBundle\Entity\MessageLog;

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
