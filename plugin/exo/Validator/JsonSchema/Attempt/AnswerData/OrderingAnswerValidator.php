<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\OrderingQuestion;
use UJM\ExoBundle\Entity\Misc\OrderingItem;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.answer_ordering")
 */
class OrderingAnswerValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'answer-data/ordering/schema.json';
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
        $errors = [];

        /** @var OrderingQuestion $question */
        $question = !empty($options[Validation::QUESTION]) ? $options[Validation::QUESTION] : null;
        if (empty($question)) {
            throw new \LogicException('Answer validation : Cannot perform additional validation without question.');
        }

        $itemIds = array_map(function (OrderingItem $item) {
            return $item->getUuid();
        }, $question->getItems()->toArray());

        foreach ($answerData as $index => $answer) {
            if (!in_array($answer->itemId, $itemIds)) {
                $errors[] = [
                    'path' => "/[{$index}]",
                    'message' => 'Answer array identifiers must reference question items',
                ];
            }
        }

        return $errors;
    }
}
