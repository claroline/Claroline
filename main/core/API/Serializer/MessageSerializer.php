<?php

namespace Claroline\CoreBundle\API\Serializer;

use Claroline\AppBundle\API\Options;
use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\API\SerializerProvider;
use Claroline\CoreBundle\Entity\AbstractMessage;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.message")
 */
class MessageSerializer
{
    use SerializerTrait;

    /**
     * @DI\InjectParams({
     *      "provider" = @DI\Inject("claroline.api.serializer")
     * })
     *
     * @param SerializerProvider $serializer
     */
    public function __construct(SerializerProvider $provider)
    {
        $this->serializerProvider = $provider;
    }

    public function getClass()
    {
        return 'Claroline\CoreBundle\Entity\AbstractMessage';
    }

    /**
     * @return string
     */
    public function getSchema()
    {
        return '#/main/core/message.json';
    }

    /**
     * @return string
     */
    /*
    public function getSamples()
    {
       return '#/main/core/message';
    }*/

    /**
     * Serializes a AbstractMessage entity.
     *
     * @param AbstractMessage $forum
     * @param array           $options
     *
     * @return array
     */
    public function serialize(AbstractMessage $message, array $options = [])
    {
        return [
            'id' => $message->getUuid(),
            'content' => $message->getContent(),
            'meta' => $this->serializeMeta($message, $options),
            'children' => $this->serializeChildren($message, $options),
            'parent' => $this->serializeParent($message, $options),
        ];
    }

    public function serializeMeta(AbstractMessage $message, array $options = [])
    {
        return [
            'creator' => $this->serializeCreator($message, $options),
            'created' => $message->getCreationDate()->format('Y-m-d\TH:i:s'),
            'updated' => $message->getModificationDate()->format('Y-m-d\TH:i:s'),
            'flagged' => $message->isFlagged(),
            'moderation' => $message->getModerated(),
        ];
    }

    public function serializeCreator(AbstractMessage $message, array $options = [])
    {
        if (!empty($message->getCreator())) {
            return $this->serializerProvider->serialize($message->getCreator(), [Options::SERIALIZE_MINIMAL]);
        }

        return [
            'name' => $message->getAuthor(),
        ];
    }

    public function serializeChildren(AbstractMessage $message, array $options = [])
    {
        $children = [];

        if ($message->getChildren()) {
            foreach ($message->getChildren()->toArray() as $child) {
                $children[] = $this->serialize($child, $options);
            }
        }

        return $children;
    }

    public function serializeParent(AbstractMessage $message, array $options = [])
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
     * @return Forum
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
                $creator = $this->serializerProvider->deserialize(
                    'Claroline\CoreBundle\Entity\User',
                    $data['meta']['creator']
                );

                if ($creator) {
                    $message->setCreator($creator);
                }
            }
        }

        return $message;
    }
}
