<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CommunityBundle\Serializer\UserSerializer;
use Claroline\CoreBundle\Entity\AbstractMessage;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;

class MessageSerializer
{
    use SerializerTrait;

    /** @var ObjectManager */
    private $om;
    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(UserSerializer $userSerializer, ObjectManager $om)
    {
        $this->userSerializer = $userSerializer;
        $this->om = $om;
    }

    public function getClass(): string
    {
        return AbstractMessage::class;
    }

    public function getName(): string
    {
        return 'abstract_message';
    }

    public function getSchema(): string
    {
        return '#/main/core/message.json';
    }

    /**
     * Serializes a AbstractMessage entity.
     */
    public function serialize(AbstractMessage $message, array $options = []): array
    {
        return [
            'id' => $message->getUuid(),
            'content' => $message->getContent(),
            'meta' => $this->serializeMeta($message),
            'parent' => $this->serializeParent($message),
            'children' => array_map(function (AbstractMessage $child) use ($options) {
                return $this->serialize($child, $options);
            }, $message->getChildren()->toArray()),
        ];
    }

    protected function serializeMeta(AbstractMessage $message): array
    {
        return [
            'creator' => $this->serializeCreator($message),
            'created' => $message->getCreationDate()->format('Y-m-d\TH:i:s'),
            'updated' => $message->getModificationDate()->format('Y-m-d\TH:i:s'),
            'flagged' => $message->isFlagged(),
            'moderation' => $message->getModerated(),
        ];
    }

    protected function serializeCreator(AbstractMessage $message): array
    {
        if (!empty($message->getCreator())) {
            return $this->userSerializer->serialize($message->getCreator(), [Options::SERIALIZE_MINIMAL]);
        }

        return [
            'name' => $message->getAuthor(),
        ];
    }

    protected function serializeParent(AbstractMessage $message): ?array
    {
        $parent = null;
        if ($message->getParent()) {
            $parent = ['id' => $message->getParent()->getId()];
        }

        return $parent;
    }

    /**
     * Deserializes data into a Forum entity.
     *
     * @param array $data
     *
     * @return AbstractMessage
     */
    public function deserialize($data, AbstractMessage $message, array $options = [])
    {
        $this->sipe('content', 'setContent', $data, $message);

        if (isset($data['meta'])) {
            if (isset($data['meta']['updated'])) {
                $message->setModificationDate(DateNormalizer::denormalize($data['meta']['updated']));
            }

            if (isset($data['meta']['creator'])) {
                $message->setAuthor($data['meta']['creator']['name']);

                /** @var User $creator */
                $creator = $this->om->getObject($data['meta']['creator'], User::class);
                if ($creator) {
                    $message->setCreator($creator);
                }
            }
        }

        return $message;
    }
}
