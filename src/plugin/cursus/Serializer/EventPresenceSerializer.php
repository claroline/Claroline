<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerInterface;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\User;
use Claroline\CursusBundle\Entity\EventPresence;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventPresenceSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly UserSerializer $userSerializer,
        private readonly SessionSerializer $sessionSerializer,
        private readonly EventSerializer $eventSerializer
    ) {
    }

    public function serialize(EventPresence $eventPresence, array $options = []): array
    {
        if (in_array(SerializerInterface::SERIALIZE_MINIMAL, $options)) {
            return [
                'id' => $eventPresence->getUuid(),
                'user' => $this->userSerializer->serialize($eventPresence->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]),
                'status' => $eventPresence->getStatus(),
            ];
        }

        $serialized = [
            'id' => $eventPresence->getUuid(),
            'user' => $this->userSerializer->serialize($eventPresence->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]),
            'event' => $this->eventSerializer->serialize($eventPresence->getEvent(), [SerializerInterface::SERIALIZE_MINIMAL]),
            'session' => $this->sessionSerializer->serialize($eventPresence->getEvent()->getSession(), [SerializerInterface::SERIALIZE_MINIMAL]),
            'status' => $eventPresence->getStatus(),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serialized['permissions'] = [
                'open' => $this->authorization->isGranted('OPEN', $eventPresence),
                'edit' => $this->authorization->isGranted('EDIT', $eventPresence),
                'delete' => $this->authorization->isGranted('DELETE', $eventPresence),
            ];
        }

        return $serialized;
    }

    public function deserialize(array $data, EventPresence $eventPresence): EventPresence
    {
        $this->sipe('id', 'setUuid', $data, $eventPresence);
        $this->sipe('status', 'setStatus', $data, $eventPresence);

        if (isset($data['user'])) {
            $user = null;
            if (isset($data['user']['id'])) {
                $user = $this->om->getRepository(User::class)->findOneBy(['uuid' => $data['user']['id']]);
            }

            $eventPresence->setUser($user);
        }

        return $eventPresence;
    }
}
