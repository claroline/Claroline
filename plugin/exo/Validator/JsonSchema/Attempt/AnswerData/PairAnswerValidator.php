<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\PairQuestion;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.answer_pair")
 */
class PairAnswerValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'answer-data/pair/schema.json';
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

        /** @var PairQuestion $question */
        $question = !empty($options[Validation::QUESTION]) ? $options[Validation::QUESTION] : null;
        if (empty($question)) {
            throw new \LogicException('Answer validation : Cannot perform additional validation without question.');
        }

        foreach ($answerData as $rowIndex => $answerRow) {
            foreach ($answerRow as $itemIndex => $answerItem) {
                if (empty($question->getItem($answerItem))) {
                    $errors[] = [
                        'path' => "/[{$rowIndex}]/[{$itemIndex}]",
                        'message' => 'answers must reference a question item',
                    ];
                }
            }
        }

        return $errors;
    }
}
