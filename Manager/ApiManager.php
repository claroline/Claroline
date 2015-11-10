<?php

namespace UJM\ExoBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Hint;
use UJM\ExoBundle\Entity\LinkHintPaper;
use UJM\ExoBundle\Entity\Paper;
use UJM\ExoBundle\Entity\Question;
use UJM\ExoBundle\Entity\Response;
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
    private $exerciseManager;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"  = @DI\Inject("ujm.exo.json_validator"),
     *     "collector"  = @DI\Inject("ujm.exo.question_handler_collector"),
     *     "manager"    = @DI\Inject("ujm.exo.exercise_manager")
     * })
     *
     * @param ObjectManager             $om
     * @param Validator                 $validator
     * @param QuestionHandlerCollector  $collector
     * @param ExerciseManager           $manager
     */
    public function __construct(
        ObjectManager $om,
        Validator $validator,
        QuestionHandlerCollector $collector,
        ExerciseManager $manager
    )
    {
        $this->om = $om;
        $this->validator = $validator;
        $this->questionRepo = $om->getRepository('UJMExoBundle:Question');
        $this->interactionQcmRepo = $om->getRepository('UJMExoBundle:InteractionQCM');
        $this->handlerCollector = $collector;
        $this->exerciseManager = $manager;
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

        $data = new \stdClass();
        $data->id = $question->getId();
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
     * @todo actual import...
     *
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
     * Exports an exercise in JSON format.
     *
     * @param Exercise  $exercise
     * @param bool      $withSolutions
     * @return array
     */
    public function exportExercise(Exercise $exercise, $withSolutions = true)
    {
        return [
            'id' => $exercise->getId(),
            'meta' => $this->exportMetadata($exercise),
            'steps' => $this->exportSteps($exercise, $withSolutions),
        ];
    }

    /**
     * Returns the JSON representation of an exercise with its last associated paper
     * for a given user. If no paper exists, a new one is created.
     *
     * @param Exercise  $exercise
     * @param User      $user
     * @param bool      $withSolutions
     * @return array
     */
    public function openExercise(Exercise $exercise, User $user, $withSolutions = false)
    {
        return [
            'exercise' => $this->exportExercise($exercise, $withSolutions),
            'paper' => $this->exportPaper($exercise, $user)
        ];
    }

    /**
     * Ensures the format of the answer is correct and returns a list of
     * validation errors, if any.
     *
     * @param Question  $question
     * @param mixed     $data
     * @return array
     * @throws \UJM\ExoBundle\Transfer\Json\UnregisteredHandlerException
     */
    public function validateAnswerFormat(Question $question, $data)
    {
        $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());

        return $handler->validateAnswerFormat($question, $data);
    }

    /**
     * Records or updates an answer for a given question and paper.
     *
     * @param Paper     $paper
     * @param Question  $question
     * @param mixed     $data
     * @param string    $ip
     */
    public function recordAnswer(Paper $paper, Question $question, $data, $ip)
    {
        $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());

        $response = $this->om->getRepository('UJMExoBundle:Response')
            ->findOneBy(['paper' => $paper, 'question' => $question]);

        if (!$response) {
            $response = new Response();
            $response->setPaper($paper);
            $response->setQuestion($question);
            $response->setIp($ip);
        } else {
            $response->setNbTries($response->getNbTries() + 1);
        }

        $handler->storeAnswerAndMark($question, $response, $data);
        $this->applyPenalties($paper, $question, $response);

        $this->om->persist($response);
        $this->om->flush();
    }

    /**
     * Returns whether a hint is related to a paper.
     *
     * @param Paper $paper
     * @param Hint  $hint
     * @return bool
     */
    public function hasHint(Paper $paper, Hint $hint)
    {
        $link = $this->om->getRepository('UJMExoBundle:ExerciseQuestion')->findOneBy([
            'question' => $hint->getQuestion(),
            'exercise' => $paper->getExercise()
        ]);

        return $link !== null;
    }

    /**
     * Returns the contents of a hint and records a log asserting that the hint
     * has been consulted for a given paper.
     *
     * @param Paper $paper
     * @param Hint  $hint
     * @return string
     */
    public function viewHint(Paper $paper, Hint $hint)
    {
        $log = $this->om->getRepository('UJMExoBundle:LinkHintPaper')
            ->findOneBy(['paper' => $paper, 'hint' => $hint]);

        if (!$log) {
            $log = new LinkHintPaper($hint, $paper);
            $this->om->persist($log);
            $this->om->flush();
        }

        return $hint->getValue();
    }

    /**
     * Terminates a user paper.
     *
     * @param Paper $paper
     */
    public function finishPaper(Paper $paper)
    {
        if (!$paper->getEnd()) {
            $paper->setEnd(new \DateTime());
            $this->om->flush();
        }
    }

    /**
     * Returns the papers of a user for a given exercise, in a JSON format.
     * Also includes the complete definition and solution of each question
     * associated with the exercise.
     *
     * @param Exercise  $exercise
     * @param User      $user
     * @return array
     */
    public function exportUserPapers(Exercise $exercise, User $user)
    {
        $papers = $this->om->getRepository('UJMExoBundle:Paper')
            ->findBy(['exercise' => $exercise, 'user' => $user]);

        $papers = array_map(function ($paper) {
            return [
                'id' => $paper->getId(),
                'number' => $paper->getNumPaper(),
                'start' => $paper->getStart()->format('Y-m-d H:i:s'),
                'end' => $paper->getEnd()->format('Y-m-d H:i:s'),
                'interrupted' => $paper->getInterupt(),
                'questions' => $this->exportPaperQuestions($paper)
            ];
        }, $papers);

        $questions = array_map(function ($question) {
            return $this->exportQuestion($question, true);
        }, $this->questionRepo->findByExercise($exercise));

        return [
            'questions' => $questions,
            'papers' => $papers
        ];
    }

    /**
     * @todo duration
     *
     * @param Exercise $exercise
     * @return array
     */
    private function exportMetadata(Exercise $exercise)
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
     * @todo step id
     *
     * @param Exercise  $exercise
     * @param bool      $withSolutions
     * @return array
     */
    private function exportSteps(Exercise $exercise, $withSolutions = true)
    {
        return array_map(function ($question) use ($withSolutions) {
            return [
                'id' => '(unknown)',
                'items' => [$this->exportQuestion($question, $withSolutions)]
            ];
        }, $this->questionRepo->findByExercise($exercise));
    }

    private function exportPaper(Exercise $exercise, User $user)
    {
        $repo = $this->om->getRepository('UJMExoBundle:Paper');
        $papers = $repo->findUnfinishedPapers($user, $exercise);

        if (count($papers) === 0) {
            $paper = $this->createPaper($user, $exercise);
            $questions = [];
        } else {
            $paper = $papers[0];
            $questions = $this->exportPaperQuestions($paper);
        }

        return [
            'id' => $paper->getId(),
            'questions' => $questions
        ];
    }

    private function createPaper(User $user, Exercise $exercise)
    {
        $lastPaper = $this->om->getRepository('UJMExoBundle:Paper')->findOneBy(
            ['user' => $user, 'exercise' => $exercise],
            ['start' => 'DESC']
        );

        $paperNum = $lastPaper ? $lastPaper->getNumPaper() + 1 : 1;
        $questions = $this->exerciseManager->pickQuestions($exercise);
        $order = '';

        foreach ($questions as $question) {
            $order .= $question->getId().';';
        }

        $paper = new Paper();
        $paper->setExercise($exercise);
        $paper->setUser($user);
        $paper->setNumPaper($paperNum);
        $paper->setOrdreQuestion($order);

        $this->om->persist($paper);
        $this->om->flush();

        return $paper;
    }

    private function exportPaperQuestions(Paper $paper)
    {
        $responseRepo = $this->om->getRepository('UJMExoBundle:Response');
        $linkRepo = $this->om->getRepository('UJMExoBundle:LinkHintPaper');
        $questions = $this->questionRepo->findByExercise($paper->getExercise());
        $paperQuestions = [];

        foreach ($questions as $question) {
            $handler = $this->handlerCollector->getHandlerForInteractionType($question->getType());
            // TODO: these two queries must be moved out of the loop
            $response = $responseRepo->findOneBy(['paper' => $paper, 'question' => $question]);
            $links = $linkRepo->findViewedByPaperAndQuestion($paper, $question);

            $answer = $response ? $handler->convertAnswerDetails($response) : null;
            $hints = array_map(function ($link) {
                return (string) $link->getHint()->getId();
            }, $links);

            if ($answer || count($hints) > 0) {
                $paperQuestions[] = [
                    'id' => (string) $question->getId(),
                    'answer' => $answer,
                    'hints' => $hints
                ];
            }
        }

        return $paperQuestions;
    }

    private function applyPenalties(Paper $paper, Question $question, Response $response)
    {
        $logs = $this->om->getRepository('UJMExoBundle:LinkHintPaper')
            ->findViewedByPaperAndQuestion($paper, $question);

        if (count($logs) === 0) {
            return;
        }

        $penalty = 0;

        foreach ($logs as $log) {
            $penalty += $log->getHint()->getPenalty();
        }

        $response->setMark($response->getMark() - $penalty);

        if ($response->getMark() < 0) {
            $response->setMark(0);
        }
    }
}
