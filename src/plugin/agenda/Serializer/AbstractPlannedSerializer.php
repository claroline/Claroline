<?php

namespace Claroline\AgendaBundle\Serializer;

use Claroline\AgendaBundle\Entity\AbstractPlanned;
use Claroline\AgendaBundle\Entity\Event;
use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\API\Serializer\File\PublicFileSerializer;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\File\PublicFile;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class AbstractPlannedSerializer
{
    use SerializerTrait;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    /** @var ObjectManager */
    private $om;

    /** @var PublicFileSerializer */
    private $fileSerializer;

    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        PublicFileSerializer $fileSerializer,
        UserSerializer $userSerializer
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->fileSerializer = $fileSerializer;
        $this->userSerializer = $userSerializer;
    }

    public function serialize(AbstractPlanned $planned, array $options = []): array
    {
        $event = $planned->getEvent();

        $serialized = [
            'id' => $event->getUuid(),
            'title' => $event->getName(),
            'start' => $event->getStart() ? DateNormalizer::normalize($event->getStart()) : null,
            'end' => $event->getEnd() ? DateNormalizer::normalize($event->getEnd()) : null,
            'thumbnail' => $this->serializeThumbnail($event),
            'description' => $event->getDescription(),
            'meta' => [
                'type' => $event->getType(),
                'creator' => $this->userSerializer->serialize($event->getCreator(), [Options::SERIALIZE_MINIMAL]),
            ],
            'display' => [
                'color' => $event->getColor(),
            ],
            'permissions' => [
                'edit' => $this->authorization->isGranted('EDIT', $event),
                'delete' => $this->authorization->isGranted('DELETE', $event),
            ],
        ];

        if (!in_array(Options::SERIALIZE_MINIMAL, $options)) {
            $serialized = array_merge($serialized, [
                'poster' => $this->serializePoster($event),
            ]);
        }

        return $serialized;
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

    private function serializeThumbnail(Event $event): ?array
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

    public function deserialize(array $data, AbstractPlanned $planned): AbstractPlanned
    {
        $event = $planned->getEvent();

        $this->sipe('id', 'setUuid', $data, $event);
        $this->sipe('title', 'setName', $data, $event);
        $this->sipe('display.color', 'setColor', $data, $event);
        $this->sipe('description', 'setDescription', $data, $event);

        if (isset($data['meta'])) {
            $this->sipe('meta.type', 'setType', $data, $event);

            if (isset($data['meta']['creator'])) {
                /** @var User $creator */
                $creator = $this->om->getObject($data['meta']['creator'], User::class);
                $event->setCreator($creator);
            }
        }

        if (isset($data['thumbnail']) && isset($data['thumbnail']['url'])) {
            $event->setThumbnail($data['thumbnail']['url']);
        }

        if (isset($data['start'])) {
            $event->setStart(DateNormalizer::denormalize($data['start']));
        }

        if (isset($data['end'])) {
            $event->setEnd(DateNormalizer::denormalize($data['end']));
        }

        return $planned;
    }
}
