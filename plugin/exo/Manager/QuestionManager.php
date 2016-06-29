<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Manager\ResourceManager;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerCollector;
use UJM\ExoBundle\Transfer\Json\ValidationException;
use UJM\ExoBundle\Transfer\Json\Validator;

/**
 * @DI\Service("ujm.exo.question_manager")
 */
class QuestionManager
{
    private $router;
    private $om;
    private $validator;
    private $handlerCollector;
    private $rm;

    /**
     * @DI\InjectParams({
     *     "router"     = @DI\Inject("router"),
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"  = @DI\Inject("ujm.exo.json_validator"),
     *     "collector"  = @DI\Inject("ujm.exo.question_handler_collector"),
     *     "rm"         = @DI\Inject("claroline.manager.resource_manager")
     * })
     *
     * @param UrlGeneratorInterface    $router
     * @param ObjectManager            $om
     * @param Validator                $validator
     * @param QuestionHandlerCollector $collector
     * @param ResourceManager          $rm
     */
    public function __construct(
        UrlGeneratorInterface $router,
        ObjectManager $om,
        Validator $validator,
        QuestionHandlerCollector $collector,
        ResourceManager $rm
    ) {
        $this->router = $router;
        $this->om = $om;
        $this->validator = $validator;
        $this->handlerCollector = $collector;
        $this->rm = $rm;
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
        $rm = $this->rm;

        $data = new \stdClass();
        $data->id = $question->getId();
        $data->type = $handler->getQuestionMimeType();
        $data->title = $question->getTitle();
        $data->description = $question->getDescription();
        $data->invite = $question->getInvite();
        $data->supplementary = $question->getSupplementary();
        $data->specification = $question->getSpecification();
        $data->objects = array_map(function ($object) use ($rm) {
            $resourceObjectData = new \stdClass();
            $resourceObjectData->id = (string) $object->getResourceNode()->getId();
            $resourceObjectData->type = $object->getResourceNode()->getResourceType()->getName();
            switch ($object->getResourceNode()->getResourceType()->getName()) {
                case 'text':
                    if ($rm->getResourceFromNode($object->getResourceNode())->getRevisions()[0]) {
                        $resourceObjectData->data = $rm->getResourceFromNode($object->getResourceNode())->getRevisions()[0]->getContent();
                    }
                default:
                    $resourceObjectData->url = $this->router->generate(
                        'claro_resource_open',
                        ['resourceType' => $object->getResourceNode()->getResourceType()->getName(), 'node' => $object->getResourceNode()->getId()]
                    );
            }

            return $resourceObjectData;
        }, $question->getObjects()->toArray());

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

    /**
     * Get question statistics inside an Exercise.
     *
     * @param Question $question
     * @param Exercise $exercise
     *
     * @return \stdClass
     */
    public function generateQuestionStats(Question $question, Exercise $exercise)
    {
        $questionStats = new \stdClass();

        // We load all the answers for the question (we need to get the entities as the response in DB are not processable as is)
        $answers = $this->om->getRepository('UJMExoBundle:Response')->findByExerciseAndQuestion($exercise, $question);

        // Number of Users that have seen the question in their exercise
        $questionStats->seen = count($answers);

        // Number of Users that have responded to the question (no blank answer)
        $questionStats->answered = 0;
        if (!empty($answers)) {
            /* @var Response $answer */
            for ($i = 0; $i < $questionStats->seen; ++$i) {
                if (!empty($answers[$i]->getResponse())) {
                    ++$questionStats->answered;
                } else {
                    // Remove element (to avoid processing in custom handlers)
                    unset($answers[$i]);
                }
            }

            // Let the Handler of the question type parse and compile the data
            $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());
            $questionStats->solutions = $handler->generateStats($question, $answers);
        }

        return $questionStats;
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
