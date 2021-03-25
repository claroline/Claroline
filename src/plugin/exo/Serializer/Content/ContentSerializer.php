<?php

namespace UJM\ExoBundle\Serializer\Content;

use Claroline\AppBundle\API\Serializer\SerializerTrait;

/**
 * Serializer for contents.
 */
class ContentSerializer
{
    use SerializerTrait;

    /**
     * @var ResourceContentSerializer
     */
    private $resourceContentSerializer;

    /**
     * ContentSerializer constructor.
     */
    public function __construct(ResourceContentSerializer $resourceContentSerializer)
    {
        $this->resourceContentSerializer = $resourceContentSerializer;
    }

    public function getName()
    {
        return 'exo_content';
    }

    /**
     * @param mixed $content
     *
     * @return array
     */
    public function serialize($content, array $options = [])
    {
        $node = $content->getResourceNode();

        if (!empty($node)) {
            $serialized = $this->resourceContentSerializer->serialize($node, $options);
        } else {
            $serialized = [
                'type' => 'text/html',
                'data' => $content->getData(),
            ];
        }

        return $serialized;
    }

    /**
     * @param array $data
     * @param mixed $content
     *
     * @return mixed
     */
    public function deserialize($data, $content = null, array $options = [])
    {
        if ('text/html' === $data['type'] || 'text/plain' === $data['type']) {
            // HTML is directly stored in the choice entity
            $content->setData($data['data']);
            $content->setResourceNode(null);
        } else {
            // Other types require a ResourceNode
            $node = $this->resourceContentSerializer->deserialize($content);

            $content->setData('');
            $content->setResourceNode($node);
        }

        return $content;
    }
}
