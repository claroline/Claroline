<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;
use UJM\ExoBundle\Validator\JsonSchema\Misc\KeywordValidator;

class ClozeQuestionValidator extends JsonSchemaValidator
{
    /**
     * @var KeywordValidator
     */
    private $keywordValidator;

    /**
     * WordsQuestionValidator constructor.
     *
     * @param KeywordValidator $keywordValidator
     */
    public function __construct(KeywordValidator $keywordValidator)
    {
        $this->keywordValidator = $keywordValidator;
    }

    public function getJsonSchemaUri()
    {
        return 'question/cloze/schema.json';
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
     *  - The solutions IDs are consistent with holes IDs
     *  - There is at least one solution with a positive score for each Hole.
     *
     * @param array $question
     *
     * @return array
     */
    public function validateSolutions(array $question)
    {
        $errors = [];

        // check solution IDs are consistent with hole IDs
        $holeIds = array_map(function (array $hole) {
            return $hole['id'];
        }, $question['holes']);

        if (count($question['holes']) !== count($question['solutions'])) {
            $errors[] = [
                'path' => '/solutions',
                'message' => 'there must be the same number of solutions and holes',
            ];
        }

        foreach ($question['solutions'] as $index => $solution) {
            if (!in_array($solution['holeId'], $holeIds)) {
                $errors[] = [
                    'path' => "/solutions[{$index}]",
                    'message' => "id {$solution['holeId']} doesn't match any hole id",
                ];
            }

            // Validates hole keywords
            $errors = array_merge($errors, $this->keywordValidator->validateCollection($solution['answers'], [Validation::NO_SCHEMA, Validation::VALIDATE_SCORE]));
        }

        return $errors;
    }
}
