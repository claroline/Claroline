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

    /**
     * Converts a ItemObject into a JSON-encodable structure.
     *
     * @return array
     */
    public function serialize(ItemObject $itemObject, array $options = [])
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

    public function getName()
    {
        return 'exo_item_object';
    }

    /**
     * Converts raw data into a ItemObject entity.
     *
     * @param array      $data
     * @param ItemObject $itemObject
     *
     * @return ItemObject
     */
    public function deserialize($data, ItemObject $itemObject = null, array $options = [])
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
