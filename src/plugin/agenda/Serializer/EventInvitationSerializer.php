<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AppBundle\API\Options;
use Claroline\CommunityBundle\Serializer\UserSerializer;

class EventInvitationSerializer
{
    public function getName(): string
    {
        return 'event_invitation';
    }

    public function __construct(
        private readonly UserSerializer $userSerializer
    ) {
    }

    public function serialize(EventInvitation $invitation): array
    {
        return [
            'id' => $invitation->getId(),
            'user' => $this->userSerializer->serialize($invitation->getUser(), [Options::SERIALIZE_MINIMAL]),
            'status' => $invitation->getStatus(),
        ];
    }
}
