<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\API\Serializer\User\UserSerializer;
use Claroline\CoreBundle\Entity\AbstractMessage;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.message")
 */
class MessageSerializer
{
    use SerializerTrait;

    /** @var UserSerializer */
    private $userSerializer;

    /**
     * @DI\InjectParams({
     *      "userSerializer" = @DI\Inject("claroline.serializer.user")
     * })
     *
     * @param UserSerializer $userSerializer
     */
    public function __construct(UserSerializer $userSerializer)
    {
        $this->userSerializer = $userSerializer;
    }

    public function getClass()
    {
        return AbstractMessage::class;
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/message.json';
    }

    /**
     * Serializes a AbstractMessage entity.
     *
     * @param AbstractMessage $message
     * @param array           $options
     *
     * @return array
     */
    public function serialize(AbstractMessage $message, array $options = [])
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

    protected function serializeMeta(AbstractMessage $message)
    {
        return [
            'creator' => $this->serializeCreator($message),
            'created' => $message->getCreationDate()->format('Y-m-d\TH:i:s'),
            'updated' => $message->getModificationDate()->format('Y-m-d\TH:i:s'),
            'flagged' => $message->isFlagged(),
            'moderation' => $message->getModerated(),
        ];
    }

    protected function serializeCreator(AbstractMessage $message)
    {
        if (!empty($message->getCreator())) {
            return $this->userSerializer->serialize($message->getCreator(), [Options::SERIALIZE_MINIMAL]);
        }

        return [
            'name' => $message->getAuthor(),
        ];
    }

    protected function serializeParent(AbstractMessage $message)
    {
        $parent = null;

        if ($dad = $message->getParent()) {
            $parent = ['id' => $dad->getId()];
        }

        return $parent;
    }

    /**
     * Deserializes data into a Forum entity.
     *
     * @param array           $data
     * @param AbstractMessage $message
     * @param array           $options
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
                $creator = $this->userSerializer->deserialize($data['meta']['creator']);

                if ($creator) {
                    $message->setCreator($creator);
                }
            }
        }

        return $message;
    }
}
