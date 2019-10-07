<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData;

use UJM\ExoBundle\Entity\ItemType\MatchQuestion;
use UJM\ExoBundle\Entity\Misc\Label;
use UJM\ExoBundle\Entity\Misc\Proposal;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class SetAnswerValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'answer-data/set/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param array $answerData
     * @param array $options
     *
     * @return array
     */
    public function validateAfterSchema($answerData, array $options = [])
    {
        /** @var MatchQuestion $question */
        $question = !empty($options[Validation::QUESTION]) ? $options[Validation::QUESTION] : null;
        if (empty($question)) {
            throw new \LogicException('Answer validation : Cannot perform additional validation without question.');
        }

        $errors = [];

        $proposalIds = array_map(function (Proposal $proposal) {
            return $proposal->getUuid();
        }, $question->getProposals()->toArray());

        $labelIds = array_map(function (Label $label) {
            return $label->getUuid();
        }, $question->getLabels()->toArray());

        foreach ($answerData as $answer) {
            if (!in_array($answer['itemId'], $proposalIds)) {
                $errors[] = [
                    'path' => '/itemId',
                    'message' => 'Answer `itemId` must reference an item from `items`',
                ];
            }

            if (!in_array($answer['setId'], $labelIds)) {
                $errors[] = [
                    'path' => '/setId',
                    'message' => 'Answer `setId` must reference an item from `sets`',
                ];
            }
        }

        return $errors;
    }
}
