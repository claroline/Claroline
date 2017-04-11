<?php

namespace UJM\ExoBundle\Serializer\Attempt;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\AbstractSerializer;

/**
 * Serializer for answer data.
 *
 * @DI\Service("ujm_exo.serializer.answer")
 */
class AnswerSerializer extends AbstractSerializer
{
    /**
     * Converts an Answer into a JSON-encodable structure.
     *
     * @param Answer $answer
     * @param array  $options
     *
     * @return \stdClass
     */
    public function serialize($answer, array $options = [])
    {
        $answerData = new \stdClass();

        $this->mapEntityToObject([
            'id' => 'uuid',
            'questionId' => 'questionId',
            'tries' => 'tries',
            'usedHints' => function (Answer $answer) use ($options) {
                return array_map(function ($hintId) use ($options) {
                    return $options['hints'][$hintId];
                }, $answer->getUsedHints());
            },
        ], $answer, $answerData);

        if (!empty($answer->getData())) {
            $answerData->data = json_decode($answer->getData());
        }
        // Adds user score
        if ($this->hasOption(Transfer::INCLUDE_USER_SCORE, $options)) {
            $this->mapEntityToObject([
                'score' => 'score',
                'feedback' => 'feedback',
            ], $answer, $answerData);
        }

        return $answerData;
    }

    /**
     * Converts raw data into a Answer entity.
     *
     * @param \stdClass $data
     * @param Answer    $answer
     * @param array     $options
     *
     * @return Answer
     */
    public function deserialize($data, $answer = null, array $options = [])
    {
        $answer = $answer ?: new Answer();
        $answer->setUuid($data->id);
        $answer->setQuestionId($data->questionId);

        $this->mapObjectToEntity([
            'questionId' => 'questionId',
            'tries' => 'tries',
            'score' => 'score',
            'feedback' => 'feedback',
            'usedHints' => function (Answer $answer, \stdClass $data) {
                if (!empty($data->usedHints)) {
                    foreach ($data->usedHints as $usedHint) {
                        $answer->addUsedHint($usedHint->id);
                    }
                }
            },
            'data' => function (Answer $answer, \stdClass $data) {
                if (!empty($data->data)) {
                    $answer->setData(json_encode($data->data));
                }
            },
        ], $data, $answer);

        return $answer;
    }
}
