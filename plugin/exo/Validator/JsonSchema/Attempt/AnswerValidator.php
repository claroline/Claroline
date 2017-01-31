<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Attempt;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Question\QuestionDefinitionsCollection;
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
    private $questionDefinitions;

    /**
     * QuestionValidator constructor.
     *
     * @param ObjectManager                 $om
     * @param QuestionDefinitionsCollection $questionDefinitions
     *
     * @DI\InjectParams({
     *     "om"                  = @DI\Inject("claroline.persistence.object_manager"),
     *     "questionDefinitions" = @DI\Inject("ujm_exo.collection.question_definitions")
     * })
     */
    public function __construct(
        ObjectManager $om,
        QuestionDefinitionsCollection $questionDefinitions)
    {
        $this->om = $om;
        $this->questionDefinitions = $questionDefinitions;
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

        // Checks the question exists
        $question = $this->om->getRepository('UJMExoBundle:Question\Question')->findOneBy([
            'uuid' => $answer->questionId,
        ]);
        if (empty($question)) {
            $errors[] = [
                'path' => '/questionId',
                'message' => 'question does not exist',
            ];
        } elseif (!empty($answer->data)) {
            // Forward to question type validator
            $definition = $this->questionDefinitions->get($question->getMimeType());

            $errors = array_merge(
                $errors,
                $definition->validateAnswer($answer->data, $question->getInteraction(), array_merge($options, [Validation::NO_SCHEMA]))
            );
        }

        return $errors;
    }
}
