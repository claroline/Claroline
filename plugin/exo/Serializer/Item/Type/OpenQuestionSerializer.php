<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\OpenQuestion;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;

/**
 * @DI\Service("ujm_exo.serializer.question_open")
 */
class OpenQuestionSerializer implements SerializerInterface
{
    /**
     * Converts a Open question into a JSON-encodable structure.
     *
     * @param OpenQuestion $openQuestion
     * @param array        $options
     *
     * @return \stdClass
     */
    public function serialize($openQuestion, array $options = [])
    {
        $questionData = new \stdClass();

        $questionData->contentType = 'text';
        $questionData->maxLength = $openQuestion->getAnswerMaxLength();

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $questionData->solutions = [];
        }

        return $questionData;
    }

    /**
     * Converts raw data into an Open question entity.
     *
     * @param \stdClass    $data
     * @param OpenQuestion $openQuestion
     * @param array        $options
     *
     * @return OpenQuestion
     */
    public function deserialize($data, $openQuestion = null, array $options = [])
    {
        if (empty($openQuestion)) {
            $openQuestion = new OpenQuestion();
        }

        if (isset($data->maxLength)) {
            $openQuestion->setAnswerMaxLength($data->maxLength);
        }

        return $openQuestion;
    }
}
