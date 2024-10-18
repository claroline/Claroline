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
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
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
                'signature' => $eventPresence->getSignature(),
                'validation_date' => $eventPresence->getValidationDate(),
            ];
        }

        $serialized = [
            'id' => $eventPresence->getUuid(),
            'user' => $this->userSerializer->serialize($eventPresence->getUser(), [SerializerInterface::SERIALIZE_MINIMAL]),
            'event' => $this->eventSerializer->serialize($eventPresence->getEvent(), [SerializerInterface::SERIALIZE_MINIMAL]),
            'session' => $this->sessionSerializer->serialize($eventPresence->getEvent()->getSession(), [SerializerInterface::SERIALIZE_MINIMAL]),
            'status' => $eventPresence->getStatus(),
            'signature' => $eventPresence->getSignature(),
            'validation_date' => DateNormalizer::normalize($eventPresence->getValidationDate()),
            'evidences' => $eventPresence->getEvidences(),
            'presence_updated_by' => $eventPresence->getPresenceUpdatedBy() ? $this->userSerializer->serialize($eventPresence->getPresenceUpdatedBy(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'presence_updated_at' => DateNormalizer::normalize($eventPresence->getPresenceUpdatedAt()),
            'evidence_added_by' => $eventPresence->getEvidenceAddedBy() ? $this->userSerializer->serialize($eventPresence->getEvidenceAddedBy(), [SerializerInterface::SERIALIZE_MINIMAL]) : null,
            'evidence_added_at' => DateNormalizer::normalize($eventPresence->getEvidenceAddedAt()),
        ];

        if (!in_array(SerializerInterface::SERIALIZE_TRANSFER, $options)) {
            $serialized['permissions'] = [
                'open' => $this->authorization->isGranted('OPEN', $eventPresence),
                'edit' => $this->authorization->isGranted('EDIT', $eventPresence),
                'administrate' => $this->authorization->isGranted('ADMINISTRATE', $eventPresence),
                'delete' => $this->authorization->isGranted('DELETE', $eventPresence),
            ];
        }

        return $serialized;
    }

    public function deserialize(array $data, EventPresence $eventPresence): EventPresence
    {
        $this->sipe('id', 'setUuid', $data, $eventPresence);
        $this->sipe('status', 'setStatus', $data, $eventPresence);
        $this->sipe('signature', 'setSignature', $data, $eventPresence);
        $eventPresence->setValidationDate(DateNormalizer::denormalize($data['validation_date']));

        if (isset($data['user'])) {
            $user = null;
            if (isset($data['user']['id'])) {
                $user = $this->om->getRepository(User::class)->findOneBy(['uuid' => $data['user']['id']]);
            }

            $eventPresence->setUser($user);
        }

        if (array_key_exists('evidences', $data)) {
            $eventPresence->setEvidences($data['evidences'] ?? null);
        }

        if (isset($data['presence_updated_by'])) {
            $updatedBy = $this->om->getRepository(User::class)->findOneBy(['uuid' => $data['presence_updated_by']['id']]);
            $eventPresence->setPresenceUpdatedBy($updatedBy);
        }

        if (isset($data['presence_updated_at'])) {
            $eventPresence->setPresenceUpdatedAt(DateNormalizer::denormalize($data['presence_updated_at']));
        }

        if (isset($data['evidence_added_by'])) {
            $addedBy = $this->om->getRepository(User::class)->findOneBy(['uuid' => $data['evidence_added_by']['id']]);
            $eventPresence->setEvidenceAddedBy($addedBy);
        }

        if (isset($data['evidence_added_at'])) {
            $eventPresence->setEvidenceAddedAt(DateNormalizer::denormalize($data['evidence_added_at']));
        }

        return $eventPresence;
    }
}
