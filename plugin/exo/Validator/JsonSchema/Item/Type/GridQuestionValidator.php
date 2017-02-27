<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;
use UJM\ExoBundle\Validator\JsonSchema\Misc\KeywordValidator;

/**
 * @DI\Service("ujm_exo.validator.question_grid")
 */
class GridQuestionValidator extends JsonSchemaValidator
{
    /**
     * @var KeywordValidator
     */
    private $keywordValidator;

    /**
     * WordsQuestionValidator constructor.
     *
     * @param KeywordValidator $keywordValidator
     *
     * @DI\InjectParams({
     *     "keywordValidator" = @DI\Inject("ujm_exo.validator.keyword")
     * })
     */
    public function __construct(KeywordValidator $keywordValidator)
    {
        $this->keywordValidator = $keywordValidator;
    }

    public function getJsonSchemaUri()
    {
        return 'question/grid/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param \stdClass $question
     * @param array     $options
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
     * @param \stdClass $question
     *
     * @return array
     */
    protected function validateSolutions(\stdClass $question)
    {
        $errors = [];

        // check solution IDs are consistent with cells IDs
        $cellIds = array_map(function (\stdClass $cell) {
            return $cell->id;
        }, $question->cells);

        foreach ($question->solutions as $index => $solution) {
            if (!in_array($solution->cellId, $cellIds)) {
                $errors[] = [
                    'path' => "/solutions[{$index}]",
                    'message' => "id {$solution->cellId} doesn't match any cell id",
                ];
            }
            // Validates cell choices
            $errors = array_merge(
              $errors,
              $this->keywordValidator->validateCollection($solution->answers, [Validation::NO_SCHEMA, Validation::VALIDATE_SCORE])
            );
        }

        return $errors;
    }
}
