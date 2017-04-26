<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\ContentItem;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

/**
 * @DI\Service("ujm_exo.serializer.item_content")
 */
class ContentItemSerializer implements SerializerInterface
{
    /**
     * Converts a content item into a JSON-encodable structure.
     *
     * @param ContentItem $contentItem
     * @param array       $options
     *
     * @return \stdClass
     */
    public function serialize($contentItem, array $options = [])
    {
        $itemData = new \stdClass();

        if (1 === preg_match('#^text\/[^/]+$#', $contentItem->getQuestion()->getMimeType())) {
            $itemData->data = $contentItem->getData();
        } else {
            $itemData->url = $contentItem->getData();
        }

        return $itemData;
    }

    /**
     * Converts raw data into a content item entity.
     *
     * @param \stdClass   $data
     * @param ContentItem $contentItem
     * @param array       $options
     *
     * @return ContentItem
     */
    public function deserialize($data, $contentItem = null, array $options = [])
    {
        if (empty($contentItem)) {
            $contentItem = new ContentItem();
        }

        if (isset($data->data)) {
            $contentItem->setData($data->data);
        } elseif (isset($data->url)) {
            $contentItem->setData($data->url);
        }

        return $contentItem;
    }
}
