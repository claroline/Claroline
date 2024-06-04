<?php

namespace UJM\ExoBundle\Serializer\Item;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\Item\ItemObject;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * Serializer for item object data.
 */
class ItemObjectSerializer
{
    use SerializerTrait;

    public function getName(): string
    {
        return 'exo_item_object';
    }

    public function getClass(): string
    {
        return ItemObject::class;
    }

    public function serialize(ItemObject $itemObject, array $options = []): array
    {
        $serialized = [
            'id' => $itemObject->getUuid(),
            'type' => $itemObject->getMimeType(),
        ];

        if (1 === preg_match('#^text\/[^/]+$#', $itemObject->getMimeType())) {
            $serialized['data'] = $itemObject->getData();
        } else {
            $serialized['url'] = $itemObject->getData();
        }

        return $serialized;
    }

    /**
     * Converts raw data into a ItemObject entity.
     */
    public function deserialize(array $data, ItemObject $itemObject = null, array $options = []): ItemObject
    {
        $itemObject = $itemObject ?: new ItemObject();

        $this->sipe('id', 'setUuid', $data, $itemObject);

        if (in_array(Transfer::REFRESH_UUID, $options)) {
            $itemObject->refreshUuid();
        }
        $this->sipe('type', 'setMimeType', $data, $itemObject);
        $this->sipe('url', 'setData', $data, $itemObject);
        $this->sipe('data', 'setData', $data, $itemObject);

        return $itemObject;
    }
}
