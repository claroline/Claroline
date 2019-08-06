<?php

namespace Claroline\CoreBundle\API\Serializer\ConnectionMessage;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\User\RoleSerializer;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Claroline\CoreBundle\Entity\ConnectionMessage\Slide;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
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

    /** @var RoleSerializer */
    private $roleSerializer;

    private $roleRepo;
    private $slideRepo;

    /**
     * ConnectionMessageSerializer constructor.
     *
     * @DI\InjectParams({
     *     "om"             = @DI\Inject("claroline.persistence.object_manager"),
     *     "roleSerializer" = @DI\Inject("claroline.serializer.role")
     * })
     *
     * @param ObjectManager  $om
     * @param RoleSerializer $roleSerializer
     */
    public function __construct(
        ObjectManager $om,
        RoleSerializer $roleSerializer
    ) {
        $this->om = $om;
        $this->roleSerializer = $roleSerializer;

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
                'dates' => DateRangeNormalizer::normalize(
                    $message->getAccessibleFrom(),
                    $message->getAccessibleUntil()
                ),
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'slides' => array_values(array_map(function (Slide $slide) {
                    return [
                        'id' => $slide->getUuid(),
                        'title' => $slide->getTitle(),
                        'content' => $slide->getContent(),
                        'poster' => $slide->getPoster() ? [
                            'url' => $slide->getPoster(),
                            'mimeType' => 'image/*',
                        ] : null,
                        'order' => $slide->getOrder(),
                    ];
                }, $message->getSlides()->toArray())),
                'restrictions' => [
                    'roles' => array_values(array_map(function (Role $role) {
                        return $this->roleSerializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
                    }, $message->getRoles()->toArray())),
                ],
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

        if (isset($data['restrictions'])) {
            if (isset($data['restrictions']['dates'])) {
                $dateRange = DateRangeNormalizer::denormalize($data['restrictions']['dates']);

                $message->setAccessibleFrom($dateRange[0]);
                $message->setAccessibleUntil($dateRange[1]);
            }

            if (isset($data['restrictions']['roles'])) {
                $message->emptyRoles();
                foreach ($data['restrictions']['roles'] as $roleData) {
                    /** @var Role $role */
                    $role = $this->roleRepo->findOneBy(['uuid' => $roleData['id']]);

                    if (!empty($role)) {
                        $message->addRole($role);
                    }
                }
            }
        }

        if (isset($data['slides'])) {
            /** @var Slide[] $oldSlides */
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
                $this->sipe('poster.url', 'setPoster', $slideData, $slide);

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
