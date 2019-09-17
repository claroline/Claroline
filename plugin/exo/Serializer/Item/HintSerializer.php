<?php

namespace UJM\ExoBundle\Serializer\Item;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * Serializer for hint data.
 */
class HintSerializer
{
    use SerializerTrait;

    /**
     * Converts a Hint into a JSON-encodable structure.
     *
     * @param Hint  $hint
     * @param array $options
     *
     * @return array
     */
    public function serialize(Hint $hint, array $options = [])
    {
        $serialized = [
            'id' => $hint->getUuid(),
            'penalty' => $hint->getPenalty(),
        ];

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['value'] = $hint->getData();
        }

        return $serialized;
    }

    /**
     * Converts raw data into a Hint entity.
     *
     * @param array $data
     * @param Hint  $hint
     * @param array $options
     *
     * @return Hint
     */
    public function deserialize($data, Hint $hint = null, array $options = [])
    {
        $hint = $hint ?: new Hint();
        $this->sipe('id', 'setUuid', $data, $hint);
        $this->sipe('penalty', 'setPenalty', $data, $hint);
        $this->sipe('value', 'setData', $data, $hint);

        if (in_array(Transfer::REFRESH_UUID, $options)) {
            $hint->refreshUuid();
        }

        return $hint;
    }
}
