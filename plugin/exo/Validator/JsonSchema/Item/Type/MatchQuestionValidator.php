<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class MatchQuestionValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/match/schema.json';
    }

    public function validateAfterSchema($question, array $options = [])
    {
        $errors = [];

        if (in_array(Validation::REQUIRE_SOLUTIONS, $options)) {
            $errors = $this->validateSolutions($question);
        }

        return $errors;
    }

    /**
     * Checks :
     *  - The solutions IDs are consistent with proposals and labels IDs.
     *
     * @param array $question
     *
     * @return array
     */
    public function validateSolutions(array $question)
    {
        $errors = [];

        // check solution IDs are consistent with proposals IDs
        $proposalIds = array_map(function (array $proposal) {
            return $proposal['id'];
        }, $question['firstSet']);

        $labelIds = array_map(function (array $label) {
            return $label['id'];
        }, $question['secondSet']);

        $maxScore = -1;
        foreach ($question['solutions'] as $index => $solution) {
            if (!in_array($solution['firstId'], $proposalIds)) {
                $errors[] = [
                    'path' => "/solutions[{$index}]",
                    'message' => "id {$solution['firstId']} doesn't match any proposal id",
                ];
            }

            if (!in_array($solution['secondId'], $labelIds)) {
                $errors[] = [
                    'path' => "/solutions[{$index}]",
                    'message' => "id {$solution['secondId']} doesn't match any label id",
                ];
            }

            if ($solution['score'] > $maxScore) {
                $maxScore = $solution['score'];
            }
        }

        // Checks there is a positive score solution
        if ($maxScore <= 0) {
            $errors[] = [
                'path' => '/solutions',
                'message' => 'There is no solution with a positive score',
            ];
        }

        return $errors;
    }
}
