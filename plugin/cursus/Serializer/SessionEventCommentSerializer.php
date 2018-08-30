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
use Claroline\CoreBundle\Repository\UserRepository;
use Claroline\CursusBundle\Entity\SessionEventComment;
use Claroline\CursusBundle\Repository\SessionEventRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.cursus.event.comment")
 * @DI\Tag("claroline.serializer")
 */
class SessionEventCommentSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;

    /** @var SessionEventRepository */
    private $sessionEventRepo;
    /** @var UserRepository */
    private $userRepo;

    /**
     * SessionEventCommentSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "serializer" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param ObjectManager      $om
     * @param SerializerProvider $serializer
     */
    public function __construct(ObjectManager $om, SerializerProvider $serializer)
    {
        $this->om = $om;
        $this->serializer = $serializer;

        $this->sessionEventRepo = $om->getRepository('Claroline\CursusBundle\Entity\CourseSession');
        $this->userRepo = $om->getRepository('Claroline\CoreBundle\Entity\User');
    }

    /**
     * @param SessionEventComment $eventComment
     * @param array               $options
     *
     * @return array
     */
    public function serialize(SessionEventComment $eventComment, array $options = [])
    {
        $serialized = [
            'id' => $eventComment->getUuid(),
            'content' => $eventComment->getContent(),
            'user' => $this->serializer->serialize($eventComment->getUser(), [Options::SERIALIZE_MINIMAL]),
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'meta' => [
                    'sessionEvent' => $this->serializer->serialize($eventComment->getSessionEvent(), [Options::SERIALIZE_MINIMAL]),
                    'creationDate' => DateNormalizer::normalize($eventComment->getCreationDate()),
                    'editionDate' => $eventComment->getEditionDate() ?
                        DateNormalizer::normalize($eventComment->getEditionDate()) :
                        null,
                ],
            ]);
        }

        return $serialized;
    }

    /**
     * @param array               $data
     * @param SessionEventComment $eventComment
     *
     * @return SessionEventComment
     */
    public function deserialize($data, SessionEventComment $eventComment)
    {
        $this->sipe('id', 'setUuid', $data, $eventComment);
        $this->sipe('content', 'setContent', $data, $eventComment);

        $user = $eventComment->getUser();

        if (empty($user) && isset($data['user']['id'])) {
            $user = $this->userRepo->findOneBy(['uuid' => $data['user']['id']]);

            if ($user) {
                $eventComment->setUser($user);
            }
        }
        $sessionEvent = $eventComment->getSessionEvent();

        if (empty($sessionEvent) && isset($data['meta']['sessionEvent']['id'])) {
            $sessionEvent = $this->sessionEventRepo->findOneBy(['uuid' => $data['meta']['sessionEvent']['id']]);

            if ($sessionEvent) {
                $eventComment->setSessionEvent($sessionEvent);
            }
        } else {
            $eventComment->setEditionDate(new \DateTime());
        }

        return $eventComment;
    }
}
