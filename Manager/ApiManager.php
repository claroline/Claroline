<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\InteractionQCM;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Transfer\Json\QuestionHandlerCollector;
use UJM\ExoBundle\Transfer\Json\ValidationException;
use UJM\ExoBundle\Transfer\Json\Validator;

/**
 * @DI\Service("ujm.exo.api_manager")
 */
class ApiManager
{
    private $om;
    private $validator;
    private $questionRepo;
    private $interactionQcmRepo;
    private $handlerCollector;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"  = @DI\Inject("ujm.exo.json_validator"),
     *     "collector"  = @DI\Inject("ujm.exo.question_handler_collector")
     * })
     *
     * @param ObjectManager             $om
     * @param Validator                 $validator
     * @param QuestionHandlerCollector  $collector
     */
    public function __construct(ObjectManager $om, Validator $validator, QuestionHandlerCollector $collector)
    {
        $this->om = $om;
        $this->validator = $validator;
        $this->questionRepo = $om->getRepository('UJMExoBundle:Question');
        $this->interactionQcmRepo = $om->getRepository('UJMExoBundle:InteractionQCM');
        $this->handlerCollector = $collector;
    }

    /**
     * Imports a question in a JSON-decoded format.
     *
     * @param \stdClass $data
     * @throws ValidationException  if the question is not valid
     * @throws \Exception           if the question type import is not implemented
     */
    public function importQuestion(\stdClass $data)
    {
        if (count($errors = $this->validator->validateQuestion($data)) > 0) {
            throw new ValidationException('Question is not valid', $errors);
        }

        if (null === $handler = $this->handlerCollector->getHandlerForMimeType($data->type)) {
            throw new \Exception("Import not supported for {$data->type}");
        }

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
     * Exports a question in JSON format.
     *
     * @param Question  $question
     * @param bool      $withSolution
     * @return \stdClass
     * @throws \Exception if the question type export is not implemented
     */
    public function exportQuestion(Question $question, $withSolution = true)
    {
        $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());

        if (!$handler) {
            throw new \Exception("Export not supported for {$question->getType()}");
        }

        $data = new \stdClass();
        $data->type = $handler->getQuestionMimeType();
        $data->title = $question->getTitle();

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

        $handler->convertInteractionDetails($question, $data, $withSolution);

        return $data;
    }

    /**
     * Imports an exercise in JSON format.
     *
     * @param string $data
     * @throws ValidationException if the exercise is not valid
     */
    public function importExercise($data)
    {
        $quiz = json_decode($data);

        $errors = $this->validator->validateExercise($quiz);

        if (count($errors) > 0) {
            throw new ValidationException('Exercise is not valid', $errors);
        }
    }

    /**
     * @todo add user parameter...
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    public function exportExercise(Exercise $exercise)
    {
        return [
            'id' => $exercise->getId(),
            'meta' => $this->getMetadata($exercise),
            'steps' => $this->getSteps($exercise),
        ];
    }

    /**
     * @todo add duration
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    private function getMetadata(Exercise $exercise)
    {
        $node = $exercise->getResourceNode();
        $creator = $node->getCreator();
        $authorName = sprintf('%s %s', $creator->getFirstName(), $creator->getLastName());

        return [
            'authors' => [$authorName],
            'created' => $node->getCreationDate()->format('Y-m-d H:i:s'),
            'title' => $exercise->getTitle(),
            'description' => $exercise->getDescription(),
            'pick' => $exercise->getNbQuestion(),
            'random' => $exercise->getShuffle(),
            'maxAttempts' => $exercise->getMaxAttempts(),
        ];
    }

    /**
     * @todo step id...
     * @todo add optional question description (schema)
     *
     * @param Exercise $exercise
     *
     * @return array
     */
    private function getSteps(Exercise $exercise)
    {
        $questions = $this->questionRepo->findByExercise($exercise);

        return array_map(function ($question) {
            switch ($questionType = $question->getType()) {
                case InteractionQCM::TYPE:
                    $data = $this->getQCM($question);
                    $type = 'application/x.choice+json';
                    break;
                default:
                    throw new \Exception("Export not implemented for {$questionType} type");
            }

            $step = [
                'id' => 'todo',
                'items' => [array_merge($data, [
                    'id' => $question->getId(),
                    'type' => $type,
                    'title' => $question->getTitle(),
                    'hints' => '',
                ])]
            ];

            if ($question->getFeedback()) {
                $step['items'][0]['feedback'] = $question->getFeedback();
            }

            if (count($hints = $question->getHints()->toArray()) > 0) {
                $step['items'][0]['hints'] = array_map(function ($hint) {
                    return [
                        'id' => $hint->getId(),
                        'text' => $hint->getValue(),
                        'penalty' => $hint->getPenalty(),
                    ];
                }, $hints);
            }

            return $step;
        }, $questions);
    }

    /**
     * @todo get real "multiple" value
     * @todo add solutions property
     * @todo check order of choices
     * @todo weight ?
     *
     * @param Question $question
     * @return array
     */
    private function getQCM(Question $question)
    {
        $qcm = $this->interactionQcmRepo->findOneBy(['question' => $question]);

        return [
            'multiple' => false,
            'random' => $qcm->getShuffle(),
            'choices' => array_map(function ($choice) {
                return [
                    'id' => $choice->getId(),
                    'type' => 'text/html',
                    'data' => $choice->getLabel(),
                ];
            }, $qcm->getChoices()->toArray()),
        ];
    }
}
