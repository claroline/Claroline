<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class ChoiceQuestionValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/choice/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param array $question
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
     *  - The solutions IDs are consistent with choices IDs
     *  - There is at least one solution with a positive score.
     *
     * @return array
     */
    protected function validateSolutions(array $question)
    {
        $errors = [];

        // check solution IDs are consistent with choice IDs
        $choiceIds = array_map(function (array $choice) {
            return $choice['id'];
        }, $question['choices']);

        $maxScore = -1;
        foreach ($question['solutions'] as $index => $solution) {
            if (!in_array($solution['id'], $choiceIds)) {
                $errors[] = [
                    'path' => "/solutions[{$index}]",
                    'message' => "id {$solution['id']} doesn't match any choice id",
                ];
            }

            if ($solution['score'] > $maxScore) {
                $maxScore = $solution['score'];
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
