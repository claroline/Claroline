<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

/**
 * @DI\Service("ujm_exo.validator.answer")
 */
class AnswerValidator extends JsonSchemaValidator
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var AnswerValidator
     */
    private $itemDefinitions;

    /**
     * AnswerValidator constructor.
     *
     * @param ObjectManager             $om
     * @param ItemDefinitionsCollection $itemDefinitions
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "itemDefinitions" = @DI\Inject("ujm_exo.collection.item_definitions")
     * })
     */
    public function __construct(
        ObjectManager $om,
        ItemDefinitionsCollection $itemDefinitions)
    {
        $this->om = $om;
        $this->itemDefinitions = $itemDefinitions;
    }

    public function getJsonSchemaUri()
    {
        return 'answer/schema.json';
    }

    /**
     * Performs additional validations.
     *
     * @param \stdClass $answer
     * @param array     $options
     *
     * @return array
     */
    public function validateAfterSchema($answer, array $options = [])
    {
        $errors = [];

        if (empty($options[Validation::QUESTION])) {
            $question = $this->om->getRepository('UJMExoBundle:Item\Item')->findOneBy([
                'uuid' => $answer->questionId,
            ]);

            $options[Validation::QUESTION] = $question->getInteraction();
        }

        if (empty($options[Validation::QUESTION])) {
            $errors[] = [
                'path' => '/questionId',
                'message' => 'question does not exist',
            ];
        } elseif (!empty($answer->data) && $this->itemDefinitions->isQuestionType($options[Validation::QUESTION]->getQuestion()->getMimeType())) {
            // Forward to item type validator
            $definition = $this->itemDefinitions->get($options[Validation::QUESTION]->getQuestion()->getMimeType());
            $errors = array_merge(
                $errors,
                $definition->validateAnswer($answer->data, $options[Validation::QUESTION], array_merge($options, [Validation::NO_SCHEMA]))
            );
        }

        return $errors;
    }
}
