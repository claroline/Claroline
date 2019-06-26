<?php

namespace Claroline\CoreBundle\API\Serializer\ConnectionMessage;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Claroline\CoreBundle\Entity\ConnectionMessage\Slide;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.connection.message")
 * @DI\Tag("claroline.serializer")
 */
class ConnectionMessageSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;

    /** @var SerializerProvider */
    private $serializer;

    private $roleRepo;
    private $slideRepo;

    /**
     * ConnectionMessageSerializer constructor.
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

        $this->roleRepo = $om->getRepository(Role::class);
        $this->slideRepo = $om->getRepository(Slide::class);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return ConnectionMessage::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/connection-message/message.json';
    }

    /**
     * Serializes a ConnectionMessage entity for the JSON api.
     *
     * @param ConnectionMessage $message
     * @param array             $options
     *
     * @return array
     */
    public function serialize(ConnectionMessage $message, array $options = [])
    {
        $serialized = [
            'id' => $message->getUuid(),
            'title' => $message->getTitle(),
            'type' => $message->getType(),
            'locked' => $message->isLocked(),
            'restrictions' => [
                'dates' => [
                    DateNormalizer::normalize($message->getStartDate()),
                    DateNormalizer::normalize($message->getEndDate()),
                ],
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'slides' => array_values(array_map(function (Slide $slide) {
                    return $this->serializer->serialize($slide, [Options::SERIALIZE_MINIMAL]);
                }, $message->getSlides()->toArray())),
                'roles' => array_values(array_map(function (Role $role) {
                    return $this->serializer->serialize($role);
                }, $message->getRoles()->toArray())),
            ]);
        }

        return $serialized;
    }

    /**
     * Deserializes ConnectionMessage data into entities.
     *
     * @param array             $data
     * @param ConnectionMessage $message
     *
     * @return ConnectionMessage
     */
    public function deserialize($data, ConnectionMessage $message)
    {
        $this->sipe('id', 'setUuid', $data, $message);
        $this->sipe('title', 'setTitle', $data, $message);
        $this->sipe('type', 'setType', $data, $message);
        $this->sipe('locked', 'setLocked', $data, $message);

        $startDate = isset($data['restrictions']['dates'][0]) ?
            DateNormalizer::denormalize($data['restrictions']['dates'][0]) :
            null;
        $endDate = isset($data['restrictions']['dates'][1]) ?
            DateNormalizer::denormalize($data['restrictions']['dates'][1]) :
            null;
        $message->setStartDate($startDate);
        $message->setEndDate($endDate);

        $message->emptyRoles();

        if (isset($data['roles'])) {
            foreach ($data['roles'] as $roleData) {
                $role = $this->roleRepo->findOneBy(['uuid' => $roleData['id']]);

                if (!empty($role)) {
                    $message->addRole($role);
                }
            }
        }
        if (isset($data['slides'])) {
            $oldSlides = $message->getSlides()->toArray();
            $newSlidesIds = [];

            foreach ($data['slides'] as $slideOrder => $slideData) {
                $slide = $this->slideRepo->findOneBy(['uuid' => $slideData['id']]);

                if (!$slide) {
                    $slide = new Slide();
                    $slide->setMessage($message);
                    $this->sipe('id', 'setUuid', $slideData, $slide);
                }
                $slide->setOrder($slideOrder);
                $this->sipe('content', 'setContent', $slideData, $slide);
                $this->sipe('title', 'setTitle', $slideData, $slide);
                $this->sipe('picture.url', 'setPicture', $slideData, $slide);

                $this->om->persist($slide);

                $newSlidesIds[] = $slide->getUuid();
            }
            foreach ($oldSlides as $oldSlide) {
                if (!in_array($oldSlide->getUuid(), $newSlidesIds)) {
                    $this->om->remove($oldSlide);
                }
            }
        }

        return $message;
    }
}
