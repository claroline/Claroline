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

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\LocationSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Repository\User\LocationRepository;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Session;
use Claroline\CursusBundle\Repository\EventRepository;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class EventSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var PublicFileSerializer */
    private $fileSerializer;
    /** @var LocationSerializer */
    private $locationSerializer;
    /** @var SessionSerializer */
    private $sessionSerializer;

    /** @var LocationRepository */
    private $locationRepo;
    /** @var ObjectRepository */
    private $sessionRepo;
    /** @var EventRepository */
    private $eventRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        PublicFileSerializer $fileSerializer,
        LocationSerializer $locationSerializer,
        SessionSerializer $sessionSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
        $this->locationSerializer = $locationSerializer;
        $this->sessionSerializer = $sessionSerializer;

        $this->locationRepo = $om->getRepository(Location::class);
        $this->sessionRepo = $om->getRepository(Session::class);
        $this->eventRepo = $om->getRepository(Event::class);
    }

    public function serialize(Event $event, array $options = []): array
    {
        $serialized = [
            'id' => $event->getUuid(),
            'code' => $event->getCode(),
            'name' => $event->getName(),
            'description' => $event->getDescription(),
            'thumbnail' => $this->serializeThumbnail($event),
            'permissions' => [
                'open' => $this->authorization->isGranted('OPEN', $event),
                'edit' => $this->authorization->isGranted('EDIT', $event),
                'delete' => $this->authorization->isGranted('DELETE', $event),
            ],
            'session' => $this->sessionSerializer->serialize($event->getSession(), [Options::SERIALIZE_MINIMAL]),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'poster' => $this->serializePoster($event),
                'location' => $event->getLocation() ? $this->locationSerializer->serialize($event->getLocation(), [Options::SERIALIZE_MINIMAL]) : null,
                'meta' => [
                    'locationExtra' => $event->getLocationExtra(),
                ],
                'restrictions' => [
                    'users' => $event->getMaxUsers(),
                    'dates' => DateRangeNormalizer::normalize($event->getStartDate(), $event->getEndDate()),
                ],
                'participants' => $this->eventRepo->countParticipants($event),
                'registration' => [
                    'registrationType' => $event->getRegistrationType(),
                ],
            ]);
        }

        return $serialized;
    }

    public function deserialize(array $data, Event $event): Event
    {
        $this->sipe('id', 'setUuid', $data, $event);
        $this->sipe('code', 'setCode', $data, $event);
        $this->sipe('name', 'setName', $data, $event);
        $this->sipe('description', 'setDescription', $data, $event);
        $this->sipe('meta.locationExtra', 'setLocationExtra', $data, $event);
        $this->sipe('restrictions.users', 'setMaxUsers', $data, $event);
        $this->sipe('registration.registrationType', 'setRegistrationType', $data, $event);

        if (isset($data['poster'])) {
            $event->setPoster($data['poster']['url'] ?? null);
        }

        if (isset($data['thumbnail'])) {
            $event->setThumbnail($data['thumbnail']['url'] ?? null);
        }

        if (isset($data['restrictions']['dates'])) {
            $dates = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

            $event->setStartDate($dates[0]);
            $event->setEndDate($dates[1]);
        }

        $session = $event->getSession();
        if (empty($session) && isset($data['session']['id'])) {
            /** @var Session $session */
            $session = $this->sessionRepo->findOneBy(['uuid' => $data['session']['id']]);

            if ($session) {
                $event->setSession($session);
            }
        }

        if (isset($data['location']) && isset($data['location']['id'])) {
            $location = $this->locationRepo->findOneBy(['uuid' => $data['location']['id']]);
            $event->setLocation($location);
        } else {
            $event->setLocation(null);
        }

        return $event;
    }

    private function serializePoster(Event $event)
    {
        if (!empty($event->getPoster())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $event->getPoster()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }

    private function serializeThumbnail(Event $event)
    {
        if (!empty($event->getThumbnail())) {
            /** @var PublicFile $file */
            $file = $this->om
                ->getRepository(PublicFile::class)
                ->findOneBy(['url' => $event->getThumbnail()]);

            if ($file) {
                return $this->fileSerializer->serialize($file);
            }
        }

        return null;
    }
}
