<?php

namespace UJM\ExoBundle\Serializer\Item;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Item\ItemObject;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * Serializer for item object data.
 *
 * @DI\Service("ujm_exo.serializer.item_object")
 * @DI\Tag("claroline.serializer")
 */
class ItemObjectSerializer
{
    use SerializerTrait;

    /**
     * Converts a ItemObject into a JSON-encodable structure.
     *
     * @param ItemObject $itemObject
     * @param array      $options
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

    /**
     * Converts raw data into a ItemObject entity.
     *
     * @param array      $data
     * @param ItemObject $itemObject
     * @param array      $options
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
