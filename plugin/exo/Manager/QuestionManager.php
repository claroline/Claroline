<?php

namespace UJM\ExoBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerCollector;
use UJM\ExoBundle\Transfer\Json\ValidationException;
use UJM\ExoBundle\Transfer\Json\Validator;

/**
 * @DI\Service("ujm.exo.question_manager")
 */
class QuestionManager
{
    private $om;
    private $validator;
    private $handlerCollector;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"  = @DI\Inject("ujm.exo.json_validator"),
     *     "collector"  = @DI\Inject("ujm.exo.question_handler_collector")
     * })
     *
     * @param ObjectManager            $om
     * @param Validator                $validator
     * @param QuestionHandlerCollector $collector
     */
    public function __construct(
        ObjectManager $om,
        Validator $validator,
        QuestionHandlerCollector $collector
    ) {
        $this->om = $om;
        $this->validator = $validator;
        $this->handlerCollector = $collector;
    }

    /**
     * Imports a question in a JSON-decoded format.
     *
     * @param \stdClass $data
     *
     * @throws ValidationException if the question is not valid
     * @throws \Exception          if the question type import is not implemented
     */
    public function importQuestion(\stdClass $data)
    {
        if (count($errors = $this->validator->validateQuestion($data)) > 0) {
            throw new ValidationException('Question is not valid', $errors);
        }

        $handler = $this->handlerCollector->getHandlerForMimeType($data->type);

        $question = new Question();
        $question->setTitle($data->title);
        $question->setInvite($data->title);

        if (isset($data->hints)) {
            foreach ($data->hints as $hintData) {
                $hint = new Hint();
                $hint->setValue($hintData->text);
                $hint->setPenalty($hintData->penalty);
                $question->addHint($hint);
                $this->om->persist($hint);
            }
        }

        if (isset($data->feedback)) {
            $question->setFeedback($data->feedback);
        }

        $handler->persistInteractionDetails($question, $data);
        $this->om->persist($question);
        $this->om->flush();
    }

    /**
     * Exports a question in a JSON-encodable format.
     *
     * @param Question $question
     * @param bool     $withSolution
     * @param bool     $forPaperList
     *
     * @return \stdClass
     *
     * @throws \Exception if the question type export is not implemented
     */
    public function exportQuestion(Question $question, $withSolution = true, $forPaperList = false)
    {
        $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());

        $data = new \stdClass();
        $data->id = $question->getId();
        $data->type = $handler->getQuestionMimeType();
        $data->title = $question->getTitle();
        $data->description = $question->getDescription();
        $data->invite = $question->getInvite();

        if (count($question->getHints()) > 0) {
            $data->hints = array_map(function ($hint) use ($withSolution) {
                $hintData = new \stdClass();
                $hintData->id = (string) $hint->getId();
                $hintData->penalty = $hint->getPenalty();

                if ($withSolution) {
                    $hintData->text = $hint->getValue();
                }

                return $hintData;
            }, $question->getHints()->toArray());
        }

        if ($withSolution && $question->getFeedback()) {
            $data->feedback = $question->getFeedback();
        }

        $handler->convertInteractionDetails($question, $data, $withSolution, $forPaperList);

        return $data;
    }

    public function exportQuestionAnswers(Question $question)
    {
        $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());
        // question info
        $data = new \stdClass();
        $data->id = $question->getId();
        $data->feedback = $question->getFeedback() ? $question->getFeedback() : '';

        $handler->convertQuestionAnswers($question, $data);

        return $data;
    }

    public function exportQuestionScore(Question $question, Paper $paper)
    {
        $response = $this->om
            ->getRepository('UJMExoBundle:Response')
            ->findOneBy(['paper' => $paper, 'question' => $question]);

        return $response ? $response->getMark() : 0;
    }

    /**
     * Ensures the format of the answer is correct and returns a list of
     * validation errors, if any.
     *
     * @param Question $question
     * @param mixed    $data
     *
     * @return array
     *
     * @throws \UJM\ExoBundle\Transfer\Json\UnregisteredHandlerException
     */
    public function validateAnswerFormat(Question $question, $data)
    {
        $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());

        return $handler->validateAnswerFormat($question, $data);
    }
}
