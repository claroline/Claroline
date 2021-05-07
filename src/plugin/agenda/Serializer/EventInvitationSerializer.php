<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AppBundle\API\Options;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;

class EventInvitationSerializer
{
    /** @var UserSerializer */
    private $userSerializer;

    public function getName(): string
    {
        return 'event_invitation';
    }

    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    public function serialize(EventInvitation $invitation)
    {
        return [
            'id' => $invitation->getId(),
            'user' => $this->userSerializer->serialize($invitation->getUser(), [Options::SERIALIZE_MINIMAL]),
            'status' => $invitation->getStatus(),
        ];
    }
}
