<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Misc\Cell;
use UJM\ExoBundle\Entity\QuestionType\GridQuestion;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.answer_grid")
 */
class GridAnswerValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'answer-data/grid/schema.json';
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
        /** @var GridQuestion $question */
        $question = !empty($options[Validation::QUESTION]) ? $options[Validation::QUESTION] : null;
        if (empty($question)) {
            throw new \LogicException('Answer validation : Cannot perform additional validation without question.');
        }

        $cellIds = array_map(function (Cell $cell) {
            return $cell->getUuid();
        }, $question->getCells()->toArray());

        foreach ($answerData as $answer) {
            if (empty($answer->cellId)) {
                return ['Answer `cellId` cannot be empty'];
            }

            if (!in_array($answer->cellId, $cellIds)) {
                return ['Answer array identifiers must reference question cells'];
            }
        }

        return [];
    }
}
