<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;
use UJM\ExoBundle\Validator\JsonSchema\Misc\KeywordValidator;

class WordsQuestionValidator extends JsonSchemaValidator
{
    /**
     * @var KeywordValidator
     */
    private $keywordValidator;

    /**
     * WordsQuestionValidator constructor.
     */
    public function __construct(KeywordValidator $keywordValidator)
    {
        $this->keywordValidator = $keywordValidator;
    }

    public function getJsonSchemaUri()
    {
        return 'question/words/schema.json';
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
     * Validates the solution of the question.
     * Sends the keywords collection to the keyword validator.
     *
     * @return array
     */
    protected function validateSolutions(array $question)
    {
        return $this->keywordValidator->validateCollection($question['solutions'], [Validation::NO_SCHEMA, Validation::VALIDATE_SCORE]);
    }
}
