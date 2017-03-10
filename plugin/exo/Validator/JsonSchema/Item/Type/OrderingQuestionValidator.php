<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.question_ordering")
 */
class OrderingQuestionValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/ordering/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param mixed $question
     * @param array $options
     *
     * @return array
     */
    public function validateAfterSchema($question, array $options = [])
    {
        $errors = [];

        if (in_array(Validation::REQUIRE_SOLUTIONS, $options)) {
            $errors = $this->validateSolutions($question);
        }

        return $errors;
    }

    /**
     * Validates the solution of the question.
     *
     * Checks :
     *  - The solutions IDs are consistent with item IDs
     *  - There is at least one solution with a positive score.
     *
     * @param \stdClass $question
     *
     * @return array
     */
    protected function validateSolutions(\stdClass $question)
    {
        $errors = [];

        // check solution IDs are consistent with choice IDs
        $itemIds = array_map(function (\stdClass $item) {
            return $item->id;
        }, $question->items);

        $maxScore = -1;
        foreach ($question->solutions as $index => $solution) {
            if (!in_array($solution->itemId, $itemIds)) {
                $errors[] = [
                    'path' => "/solutions[{$index}]",
                    'message' => "id {$solution->id} doesn't match any item id",
                ];
            }

            if ($solution->score > $maxScore) {
                $maxScore = $solution->score;
            }
        }

        // check there is a positive score solution
        if ($maxScore <= 0) {
            $errors[] = [
                'path' => '/solutions',
                'message' => 'There is no solution with a positive score',
            ];
        }

        return $errors;
    }
}
