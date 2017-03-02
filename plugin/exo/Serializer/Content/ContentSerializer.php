<?php

namespace UJM\ExoBundle\Serializer\Content;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

/**
 * Serializer for contents.
 *
 * @DI\Service("ujm_exo.serializer.content")
 */
class ContentSerializer implements SerializerInterface
{
    /**
     * @var ResourceContentSerializer
     */
    private $resourceContentSerializer;

    /**
     * ContentSerializer constructor.
     *
     * @DI\InjectParams({
     *     "resourceContentSerializer" = @DI\Inject("ujm_exo.serializer.resource_content")
     * })
     *
     * @param ResourceContentSerializer $resourceContentSerializer
     */
    public function __construct(ResourceContentSerializer $resourceContentSerializer)
    {
        $this->resourceContentSerializer = $resourceContentSerializer;
    }

    /**
     * @param mixed $content
     * @param array $options
     *
     * @return \stdClass
     */
    public function serialize($content, array $options = [])
    {
        $node = $content->getResourceNode();
        if (!empty($node)) {
            $contentData = $this->resourceContentSerializer->serialize($node, $options);
        } else {
            $contentData = new \stdClass();
            $contentData->type = 'text/html';
            $contentData->data = $content->getData();
        }

        return $contentData;
    }

    /**
     * @param \stdClass $data
     * @param mixed     $content
     * @param array     $options
     *
     * @return mixed
     */
    public function deserialize($data, $content = null, array $options = [])
    {
        if ('text/html' === $data->type || 'text/plain' === $data->type) {
            // HTML is directly stored in the choice entity
            $content->setData($data->data);
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
