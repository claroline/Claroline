<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\ContentItem;

class ContentItemSerializer
{
    use SerializerTrait;

    public function getName(): string
    {
        return 'exo_question_content_item';
    }

    public function serialize(ContentItem $contentItem, array $options = []): array
    {
        $serialized = [];

        if (1 === preg_match('#^text\/[^/]+$#', $contentItem->getQuestion()->getMimeType())) {
            $serialized['data'] = $contentItem->getData();
        } elseif (1 === preg_match('#^resource\/[^/]+$#', $contentItem->getQuestion()->getMimeType())) {
            $serialized['resource'] = json_decode($contentItem->getData(), true);
        } else {
            $serialized['url'] = $contentItem->getData();
        }

        return $serialized;
    }

    public function deserialize(array $data, ?ContentItem $contentItem = null, ?array $options = []): ContentItem
    {
        if (empty($contentItem)) {
            $contentItem = new ContentItem();
        }
        $this->sipe('url', 'setData', $data, $contentItem);
        $this->sipe('data', 'setData', $data, $contentItem);

        if (isset($data['resource'])) {
            $contentItem->setData(json_encode($data['resource']));
        }

        return $contentItem;
    }
}
