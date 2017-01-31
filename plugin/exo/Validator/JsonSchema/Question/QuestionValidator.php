<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Question;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Question\QuestionDefinitionsCollection;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.question")
 */
class QuestionValidator extends JsonSchemaValidator
{
    /**
     * @var QuestionDefinitionsCollection
     */
    private $questionDefinitions;

    /**
     * @var CategoryValidator
     */
    private $categoryValidator;

    /**
     * @var HintValidator
     */
    private $hintValidator;

    /**
     * QuestionValidator constructor.
     *
     * @param QuestionDefinitionsCollection $questionDefinitions
     * @param CategoryValidator             $categoryValidator
     * @param HintValidator                 $hintValidator
     *
     * @DI\InjectParams({
     *     "questionDefinitions" = @DI\Inject("ujm_exo.collection.question_definitions"),
     *     "categoryValidator"   = @DI\Inject("ujm_exo.validator.category"),
     *     "hintValidator"       = @DI\Inject("ujm_exo.validator.hint")
     * })
     */
    public function __construct(
        QuestionDefinitionsCollection $questionDefinitions,
        CategoryValidator $categoryValidator,
        HintValidator $hintValidator)
    {
        $this->questionDefinitions = $questionDefinitions;
        $this->categoryValidator = $categoryValidator;
        $this->hintValidator = $hintValidator;
    }

    public function getJsonSchemaUri()
    {
        return 'question/base/schema.json';
    }

    /**
     * Delegates the validation to the correct question type handler.
     *
     * @param \stdClass $question
     * @param array     $options
     *
     * @return array
     */
    public function validateAfterSchema($question, array $options = [])
    {
        $errors = [];

        if (empty($question->content)) {
            // No blank content
            $errors[] = [
                'path' => '/content',
                'message' => 'Question content can not be empty',
            ];
        }

        if (!isset($question->score)) {
            // No question with no score
            // this is not in the schema because this will become optional when exercise without scores will be implemented
            $errors[] = [
                'path' => '/score',
                'message' => 'Question score is required',
            ];
        }

        if (in_array(Validation::REQUIRE_SOLUTIONS, $options) && !isset($question->solutions)) {
            // No question without solutions
            $errors[] = [
                'path' => '/solutions',
                'message' => 'Question requires a "solutions" property',
            ];
        }

        if (!$this->questionDefinitions->has($question->type)) {
            $errors[] = [
                'path' => '/type',
                'message' => 'Unknown question type "'.$question->type.'"',
            ];
        }

        // Validate category
        if (isset($question->meta) && isset($question->meta->category)) {
            $errors = array_merge($errors, $this->categoryValidator->validateAfterSchema($question->meta->category, $options));
        }

        // Validate hints
        if (isset($question->hints)) {
            array_map(function ($hint) use (&$errors, $options) {
                $errors = array_merge($errors, $this->hintValidator->validateAfterSchema($hint, $options));
            }, $question->hints);
        }

        // Validates specific data of the question type
        if (empty($errors)) {
            // Forward to the correct definition
            $definition = $this->questionDefinitions->get($question->type);

            $errors = array_merge(
                $errors,
                $definition->validateQuestion($question, array_merge($options, [Validation::NO_SCHEMA]))
            );
        }

        return $errors;
    }
}
