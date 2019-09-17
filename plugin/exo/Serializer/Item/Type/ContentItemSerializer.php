<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\ContentItem;

class ContentItemSerializer
{
    use SerializerTrait;

    /**
     * Converts a content item into a JSON-encodable structure.
     *
     * @param ContentItem $contentItem
     * @param array       $options
     *
     * @return array
     */
    public function serialize(ContentItem $contentItem, array $options = [])
    {
        $serialized = [];

        if (1 === preg_match('#^text\/[^/]+$#', $contentItem->getQuestion()->getMimeType())) {
            $serialized['data'] = $contentItem->getData();
        } else {
            $serialized['url'] = $contentItem->getData();
        }

        return $serialized;
    }

    /**
     * Converts raw data into a content item entity.
     *
     * @param array       $data
     * @param ContentItem $contentItem
     * @param array       $options
     *
     * @return ContentItem
     */
    public function deserialize($data, ContentItem $contentItem = null, array $options = [])
    {
        if (empty($contentItem)) {
            $contentItem = new ContentItem();
        }
        $this->sipe('url', 'setData', $data, $contentItem);
        $this->sipe('data', 'setData', $data, $contentItem);

        return $contentItem;
    }
}
