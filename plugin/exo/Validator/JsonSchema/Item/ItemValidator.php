<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;
use UJM\ExoBundle\Validator\JsonSchema\Content\ContentValidator;

/**
 * @DI\Service("ujm_exo.validator.item")
 */
class ItemValidator extends JsonSchemaValidator
{
    /**
     * @var ItemDefinitionsCollection
     */
    private $itemDefinitions;

    /**
     * @var CategoryValidator
     */
    private $categoryValidator;

    /**
     * @var HintValidator
     */
    private $hintValidator;

    /**
     * @var ContentValidator
     */
    private $contentValidator;

    /**
     * ItemValidator constructor.
     *
     * @param ItemDefinitionsCollection $itemDefinitions
     * @param CategoryValidator         $categoryValidator
     * @param HintValidator             $hintValidator
     * @param ContentValidator          $contentValidator
     *
     * @DI\InjectParams({
     *     "itemDefinitions"   = @DI\Inject("ujm_exo.collection.item_definitions"),
     *     "categoryValidator" = @DI\Inject("ujm_exo.validator.category"),
     *     "hintValidator"     = @DI\Inject("ujm_exo.validator.hint"),
     *     "contentValidator"  = @DI\Inject("ujm_exo.validator.content")
     * })
     */
    public function __construct(
        ItemDefinitionsCollection $itemDefinitions,
        CategoryValidator $categoryValidator,
        HintValidator $hintValidator,
        ContentValidator $contentValidator)
    {
        $this->itemDefinitions = $itemDefinitions;
        $this->categoryValidator = $categoryValidator;
        $this->hintValidator = $hintValidator;
        $this->contentValidator = $contentValidator;
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

        if (!$this->itemDefinitions->has($question->type)) {
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

        // Validate objects
        if (isset($question->objects)) {
            array_map(function ($object) use (&$errors, $options) {
                $errors = array_merge($errors, $this->contentValidator->validateAfterSchema($object, $options));
            }, $question->objects);
        }

        // Validates specific data of the question type
        if (empty($errors)) {
            // Forward to the correct definition
            $definition = $this->itemDefinitions->get($question->type);

            $errors = array_merge(
                $errors,
                $definition->validateQuestion($question, array_merge($options, [Validation::NO_SCHEMA]))
            );
        }

        return $errors;
    }
}
