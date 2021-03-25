<?php

namespace UJM\ExoBundle\Serializer\Attempt;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Library\Options\Score;
use UJM\ExoBundle\Library\Options\Transfer;

/**
 * Serializer for answer data.
 */
class AnswerSerializer
{
    use SerializerTrait;

    /**
     * Converts an Answer into a JSON-encodable structure.
     *
     * @return array
     */
    public function serialize(Answer $answer, array $options = [])
    {
        $usedHints = [];
        if (isset($options['hints'])) {
            foreach ($answer->getUsedHints() as $hintId) {
                if (isset($options['hints'][$hintId])) {
                    $usedHints[] = $options['hints'][$hintId];
                }
            }
        }

        $serialized = [
            'id' => $answer->getUuid(),
            'questionId' => $answer->getQuestionId(),
            'tries' => $answer->getTries(),
            'usedHints' => $usedHints,
        ];

        if (!empty($answer->getData())) {
            $serialized['data'] = json_decode($answer->getData(), true);
        }

        // Adds user score
        if (in_array(Transfer::INCLUDE_USER_SCORE, $options)) {
            $score = $answer->getScore();
            if ($score) {
                $score = round($score, Score::PRECISION);
            }

            $serialized = array_merge($serialized, [
                'score' => $score,
                'feedback' => $answer->getFeedback(),
            ]);
        }

        return $serialized;
    }

    public function getName()
    {
        return 'exo_answer';
    }

    /**
     * Converts raw data into a Answer entity.
     *
     * @param array  $data
     * @param Answer $answer
     *
     * @return Answer
     */
    public function deserialize($data, Answer $answer = null, array $options = [])
    {
        $answer = $answer ?: new Answer();

        $this->sipe('id', 'setUuid', $data, $answer);
        $this->sipe('questionId', 'setQuestionId', $data, $answer);
        $this->sipe('tries', 'setTries', $data, $answer);
        $this->sipe('score', 'setScore', $data, $answer);
        $this->sipe('feedback', 'setFeedback', $data, $answer);

        if (isset($data['usedHints'])) {
            foreach ($data['usedHints'] as $usedHint) {
                $answer->addUsedHint($usedHint['id']);
            }
        }

        if (!empty($data['data'])) {
            $answer->setData(json_encode($data['data']));
        }

        return $answer;
    }
}
