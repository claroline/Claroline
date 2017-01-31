<?php

namespace UJM\ExoBundle\Library\Question\Definition;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Misc\Association;
use UJM\ExoBundle\Entity\QuestionType\AbstractQuestion;
use UJM\ExoBundle\Entity\QuestionType\MatchQuestion;
use UJM\ExoBundle\Library\Attempt\CorrectedAnswer;
use UJM\ExoBundle\Library\Attempt\GenericPenalty;
use UJM\ExoBundle\Library\Question\QuestionType;
use UJM\ExoBundle\Serializer\Question\Type\SetQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\SetAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Question\Type\SetQuestionValidator;

/**
 * Set question definition.
 *
 * @DI\Service("ujm_exo.definition.question_set")
 * @DI\Tag("ujm_exo.definition.question")
 */
class SetDefinition extends AbstractDefinition
{
    /**
     * @var SetQuestionValidator
     */
    private $validator;

    /**
     * @var SetAnswerValidator
     */
    private $answerValidator;

    /**
     * @var SetQuestionSerializer
     */
    private $serializer;

    /**
     * SetDefinition constructor.
     *
     * @param SetQuestionValidator  $validator
     * @param SetAnswerValidator    $answerValidator
     * @param SetQuestionSerializer $serializer
     *
     * @DI\InjectParams({
     *     "validator"       = @DI\Inject("ujm_exo.validator.question_set"),
     *     "answerValidator" = @DI\Inject("ujm_exo.validator.answer_set"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.question_set")
     * })
     */
    public function __construct(
        SetQuestionValidator $validator,
        SetAnswerValidator $answerValidator,
        SetQuestionSerializer $serializer)
    {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the set question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return QuestionType::SET;
    }

    /**
     * Gets the set question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\QuestionType\MatchQuestion';
    }

    /**
     * Gets the set question validator.
     *
     * @return SetQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the set answer validator.
     *
     * @return SetAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the set question serializer.
     *
     * @return SetQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param MatchQuestion $question
     * @param array         $answer
     *
     * @return CorrectedAnswer
     */
    public function correctAnswer(AbstractQuestion $question, $answer)
    {
        $corrected = new CorrectedAnswer();

        if (is_array($answer)) {
            foreach ($question->getAssociations() as $association) {
                $found = false;
                foreach ($answer as $index => $givenAnswer) {
                    if (null !== $association->getLabel()
                        && $association->getLabel()->getUuid() === $givenAnswer->setId
                        && $association->getProposal()->getUuid() === $givenAnswer->itemId
                    ) {
                        $found = true;
                        if (0 < $association->getScore()) {
                            $corrected->addExpected($association);
                        } else {
                            $corrected->addUnexpected($association);
                        }

                        unset($answer[$index]);
                    }
                }

                if (!$found && 0 < $association->getScore()) {
                    $corrected->addMissing($association);
                }
            }

            if (!empty($answer) && $question->getPenalty()) {
                // there are association not defined in the exercise
                $corrected->addPenalty(
                    new GenericPenalty(count($answer) * $question->getPenalty())
                );
            }
        }

        return $corrected;
    }

    /**
     * @param MatchQuestion $question
     *
     * @return array
     */
    public function expectAnswer(AbstractQuestion $question)
    {
        return array_filter($question->getAssociations()->toArray(), function (Association $association) {
            return 0 < $association->getScore();
        });
    }

    public function getStatistics(AbstractQuestion $setQuestion, array $answers)
    {
        // TODO: Implement getStatistics() method.

        return [];
    }
}
