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
use Claroline\CursusBundle\Entity\SessionEventSet;
use Claroline\CursusBundle\Repository\CourseSessionRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.cursus.event.set")
 * @DI\Tag("claroline.serializer")
 */
class SessionEventSetSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var SessionSerializer */
    private $sessionSerializer;

    /** @var CourseSessionRepository */
    private $sessionRepo;

    /**
     * SessionEventSetSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"                = @DI\Inject("claroline.persistence.object_manager"),
     *     "sessionSerializer" = @DI\Inject("claroline.serializer.cursus.session")
     * })
     *
     * @param ObjectManager     $om
     * @param SessionSerializer $sessionSerializer
     */
    public function __construct(ObjectManager $om, SessionSerializer $sessionSerializer)
    {
        $this->om = $om;
        $this->sessionSerializer = $sessionSerializer;

        $this->sessionRepo = $om->getRepository('Claroline\CursusBundle\Entity\CourseSession');
    }

    /**
     * @param SessionEventSet $eventSet
     * @param array           $options
     *
     * @return array
     */
    public function serialize(SessionEventSet $eventSet, array $options = [])
    {
        $serialized = [
            'id' => $eventSet->getUuid(),
            'name' => $eventSet->getName(),
            'limit' => $eventSet->getLimit(),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => [
                    'session' => $this->sessionSerializer->serialize($eventSet->getSession(), [Options::SERIALIZE_MINIMAL]),
                ],
            ]);
        }

        return $serialized;
    }

    /**
     * @param array           $data
     * @param SessionEventSet $eventSet
     *
     * @return SessionEventSet
     */
    public function deserialize($data, SessionEventSet $eventSet)
    {
        $this->sipe('id', 'setUuid', $data, $eventSet);
        $this->sipe('name', 'setName', $data, $eventSet);
        $this->sipe('limit', 'setLimit', $data, $eventSet);

        $session = $eventSet->getSession();

        if (empty($session) && isset($data['meta']['session']['id'])) {
            $session = $this->sessionRepo->findOneBy(['uuid' => $data['meta']['session']['id']]);

            if ($session) {
                $eventSet->setSession($session);
            }
        }

        return $eventSet;
    }
}
