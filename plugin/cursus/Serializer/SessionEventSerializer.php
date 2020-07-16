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
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Location;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\CoreBundle\Repository\Organization\LocationRepository;
use Claroline\CursusBundle\Entity\CourseSession;
use Claroline\CursusBundle\Entity\SessionEvent;
use Doctrine\Common\Persistence\ObjectRepository;

class SessionEventSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    /** @var LocationRepository */
    private $locationRepo;
    /** @var ObjectRepository */
    private $sessionRepo;

    /**
     * SessionEventSerializer constructor.
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->om = $om;
        $this->serializer = $serializer;

        $this->locationRepo = $om->getRepository(Location::class);
        $this->sessionRepo = $om->getRepository(CourseSession::class);
    }

    /**
     * @param SessionEvent $event
     * @param array        $options
     *
     * @return array
     */
    public function serialize(SessionEvent $event, array $options = [])
    {
        $serialized = [
            'id' => $event->getUuid(),
            'code' => $event->getCode(),
            'name' => $event->getName(),
            'description' => $event->getDescription(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => [
                    'type' => $event->getType(),
                    'session' => $this->serializer->serialize($event->getSession(), [Options::SERIALIZE_MINIMAL]),
                    'location' => $event->getLocation() ? $this->serializer->serialize($event->getLocation()) : null,
                    'locationExtra' => $event->getLocationExtra(),
                    'isEvent' => SessionEvent::TYPE_EVENT === $event->getType(),
                ],
                'restrictions' => [
                    'maxUsers' => $event->getMaxUsers(),
                    'dates' => DateRangeNormalizer::normalize($event->getStartDate(), $event->getEndDate()),
                ],
                'registration' => [
                    'registrationType' => $event->getRegistrationType(),
                ],
            ]);
        }

        return $serialized;
    }

    /**
     * @param array        $data
     * @param SessionEvent $event
     *
     * @return SessionEvent
     */
    public function deserialize($data, SessionEvent $event)
    {
        $this->sipe('id', 'setUuid', $data, $event);
        $this->sipe('code', 'setCode', $data, $event);
        $this->sipe('name', 'setName', $data, $event);
        $this->sipe('description', 'setDescription', $data, $event);

        $this->sipe('meta.type', 'setType', $data, $event);
        $this->sipe('meta.locationExtra', 'setLocationExtra', $data, $event);

        $this->sipe('restrictions.maxUsers', 'setMaxUsers', $data, $event);

        $this->sipe('registration.registrationType', 'setRegistrationType', $data, $event);

        if (isset($data['restrictions']['dates'])) {
            $dates = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

            $event->setStartDate($dates[0]);
            $event->setEndDate($dates[1]);
        }

        $session = $event->getSession();
        if (empty($session) && isset($data['meta']['session']['id'])) {
            /** @var CourseSession $session */
            $session = $this->sessionRepo->findOneBy(['uuid' => $data['meta']['session']['id']]);

            if ($session) {
                $event->setSession($session);
            }
        }

        if (isset($data['meta']['location']) && isset($data['meta']['location']['id'])) {
            $location = $this->locationRepo->findOneBy(['uuid' => $data['meta']['location']['id']]);
            $event->setLocation($location);
        } else {
            $event->setLocation(null);
        }

        return $event;
    }
}
