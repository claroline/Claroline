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

    public function getName(): string
    {
        return 'exo_hint';
    }

    public function getClass(): string
    {
        return Hint::class;
    }

    public function serialize(Hint $hint, array $options = []): array
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

    public function deserialize(array $data, Hint $hint = null, array $options = []): Hint
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
