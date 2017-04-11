<?php

namespace UJM\ExoBundle\Serializer\Item;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Item\Hint;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

/**
 * Serializer for hint data.
 *
 * @DI\Service("ujm_exo.serializer.hint")
 */
class HintSerializer implements SerializerInterface
{
    /**
     * Converts a Hint into a JSON-encodable structure.
     *
     * @param Hint  $hint
     * @param array $options
     *
     * @return \stdClass
     */
    public function serialize($hint, array $options = [])
    {
        $hintData = new \stdClass();
        $hintData->id = $hint->getUuid();

        if (0 !== $hint->getPenalty()) {
            $hintData->penalty = $hint->getPenalty();
        }

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $hintData->value = $hint->getData();
        }

        return $hintData;
    }

    /**
     * Converts raw data into a Hint entity.
     *
     * @param \stdClass $data
     * @param Hint      $hint
     * @param array     $options
     *
     * @return Hint
     */
    public function deserialize($data, $hint = null, array $options = [])
    {
        $hint = $hint ?: new Hint();
        $hint->setUuid($data->id);

        if (!empty($data->penalty) || 0 === $data->penalty) {
            $hint->setPenalty($data->penalty);
        }

        $hint->setData($data->value);

        return $hint;
    }
}
