<?php

namespace Claroline\CoreBundle\API\Serializer\Platform;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Manager\PlatformManager;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\RoleSerializer;
use Claroline\CoreBundle\Entity\ConnectionMessage\ConnectionMessage;
use Claroline\CoreBundle\Entity\ConnectionMessage\Slide;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Doctrine\Persistence\ObjectRepository;

class ConnectionMessageSerializer
{
    use SerializerTrait;

    private ObjectRepository $roleRepo;
    private ObjectRepository $slideRepo;

    public function __construct(
        private readonly ObjectManager $om,
        private readonly PlatformManager $platformManager,
        private readonly RoleSerializer $roleSerializer
    ) {
        $this->roleRepo = $om->getRepository(Role::class);
        $this->slideRepo = $om->getRepository(Slide::class);
    }

    public function getClass(): string
    {
        return ConnectionMessage::class;
    }

    public function getSchema(): string
    {
        return '#/main/core/connection-message/message.json';
    }

    /**
     * Serializes a ConnectionMessage entity for the JSON api.
     */
    public function serialize(ConnectionMessage $message, array $options = []): array
    {
        $serialized = [
            'id' => $message->getUuid(),
            'title' => $message->getTitle(),
            'type' => $message->getType(),
            'locked' => $message->isLocked(),
            'restrictions' => [
                'hidden' => $message->isHidden(),
                'dates' => DateRangeNormalizer::normalize(
                    $message->getAccessibleFrom(),
                    $message->getAccessibleUntil()
                ),
                'roles' => array_values(array_map(function (Role $role) {
                    return $this->roleSerializer->serialize($role, [Options::SERIALIZE_MINIMAL]);
                }, $message->getRoles()->toArray())),
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'slides' => array_values(array_map(function (Slide $slide) use ($options) {
                    $poster = null;
                    if ($slide->getPoster()) {
                        if (in_array(Options::ABSOLUTE_URL, $options)) {
                            $poster = $this->platformManager->getUrl().'/'.$slide->getPoster();
                        } else {
                            $poster = $slide->getPoster();
                        }
                    }

                    return [
                        'id' => $slide->getUuid(),
                        'title' => $slide->getTitle(),
                        'content' => $slide->getContent(),
                        'poster' => $poster,
                        'order' => $slide->getOrder(),
                        'shortcuts' => $slide->getShortcuts(),
                    ];
                }, $message->getSlides()->toArray())),
            ]);
        }

        return $serialized;
    }

    /**
     * Deserializes ConnectionMessage data into entities.
     */
    public function deserialize(array $data, ConnectionMessage $message): ConnectionMessage
    {
        $this->sipe('id', 'setUuid', $data, $message);
        $this->sipe('title', 'setTitle', $data, $message);
        $this->sipe('type', 'setType', $data, $message);
        $this->sipe('locked', 'setLocked', $data, $message);

        if (isset($data['restrictions'])) {
            $this->sipe('restrictions.hidden', 'setHidden', $data, $message);

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
                $slide = null;
                if ($slideData['id']) {
                    $slide = $this->slideRepo->findOneBy(['uuid' => $slideData['id']]);
                }

                if (!$slide) {
                    $slide = new Slide();
                }

                $this->sipe('id', 'setUuid', $slideData, $slide);
                $this->sipe('content', 'setContent', $slideData, $slide);
                $this->sipe('title', 'setTitle', $slideData, $slide);
                $this->sipe('poster', 'setPoster', $slideData, $slide);
                $this->sipe('shortcuts', 'setShortcuts', $slideData, $slide);

                $slide->setOrder($slideOrder);
                $message->addSlide($slide);

                $newSlidesIds[] = $slide->getUuid();
            }

            foreach ($oldSlides as $oldSlide) {
                if (!in_array($oldSlide->getUuid(), $newSlidesIds)) {
                    $message->removeSlide($oldSlide);
                }
            }
        }

        return $message;
    }
}
