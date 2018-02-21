<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Library\Validator\ValidationException;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\ExerciseRepository;
use UJM\ExoBundle\Serializer\ExerciseSerializer;
use UJM\ExoBundle\Transfer\Parser\ContentParserInterface;
use UJM\ExoBundle\Validator\JsonSchema\ExerciseValidator;

/**
 * @DI\Service("ujm_exo.manager.exercise")
 */
class ExerciseManager
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var ExerciseRepository
     */
    private $repository;

    /**
     * @var ExerciseValidator
     */
    private $validator;

    /**
     * @var ExerciseSerializer
     */
    private $serializer;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * @var PaperManager
     */
    private $paperManager;

    /**
     * @var ItemDefinitionsCollection
     */
    private $definitions;

    /**
     * ExerciseManager constructor.
     *
     * @DI\InjectParams({
     *     "om"           = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"    = @DI\Inject("ujm_exo.validator.exercise"),
     *     "serializer"   = @DI\Inject("ujm_exo.serializer.exercise"),
     *     "itemManager"  = @DI\Inject("ujm_exo.manager.item"),
     *     "paperManager" = @DI\Inject("ujm_exo.manager.paper"),
     *     "definitions"  = @DI\Inject("ujm_exo.collection.item_definitions")
     * })
     *
     * @param ObjectManager      $om
     * @param ExerciseValidator  $validator
     * @param ExerciseSerializer $serializer
     * @param ItemManager        $itemManager
     * @param PaperManager       $paperManager
     */
    public function __construct(
        ObjectManager $om,
        ExerciseValidator $validator,
        ExerciseSerializer $serializer,
        ItemManager $itemManager,
        PaperManager $paperManager,
        ItemDefinitionsCollection $definitions
    ) {
        $this->om = $om;
        $this->repository = $this->om->getRepository('UJMExoBundle:Exercise');
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->itemManager = $itemManager;
        $this->paperManager = $paperManager;
        $this->definitions = $definitions;
    }

    /**
     * Validates and creates a new Exercise from raw data.
     *
     * @param \stdClass $data
     *
     * @return Exercise
     *
     * @throws ValidationException
     */
    public function create(\stdClass $data)
    {
        return $this->update(new Exercise(), $data);
    }

    /**
     * Validates and updates an Exercise entity with raw data.
     *
     * @param Exercise  $exercise
     * @param \stdClass $data
     *
     * @return Exercise
     *
     * @throws ValidationException
     */
    public function update(Exercise $exercise, \stdClass $data)
    {
        // Validate received data
        $errors = $this->validator->validate($data, [Validation::REQUIRE_SOLUTIONS]);
        if (count($errors) > 0) {
            throw new ValidationException('Exercise is not valid', $errors);
        }
        // Start flush suite to avoid persisting and flushing tags before quiz
        $this->om->startFlushSuite();
        // Update Exercise with new data
        $this->serializer->deserialize($data, $exercise, [Transfer::PERSIST_TAG]);

        // Save to DB
        $this->om->persist($exercise);
        $this->om->endFlushSuite();

        // Invalidate unfinished papers
        $this->repository->invalidatePapers($exercise);

        return $exercise;
    }

    /**
     * Serializes an Exercise.
     *
     * @param Exercise $exercise
     * @param array    $options
     *
     * @return \stdClass
     */
    public function serialize(Exercise $exercise, array $options = [])
    {
        return $this->serializer->serialize($exercise, $options);
    }

    /**
     * Copies an Exercise resource.
     *
     * @param Exercise $exercise
     *
     * @return Exercise
     */
    public function copy(Exercise $exercise)
    {
        // Serialize quiz entities
        $exerciseData = $this->serializer->serialize($exercise, [Transfer::INCLUDE_SOLUTIONS]);

        // Populate new entities with original data
        $newExercise = $this->createCopy($exerciseData, null);

        // need to init publishedOnce according to current publication state
        $newExercise->setPublishedOnce($exercise->getResourceNode()->isPublished());

        // Save copy to db
        $this->om->flush();

        return $newExercise;
    }

    /**
     * Checks if an Exercise can be deleted.
     * The exercise needs to be unpublished or have no paper to be safely removed.
     *
     * @param Exercise $exercise
     *
     * @return bool
     */
    public function isDeletable(Exercise $exercise)
    {
        return !$exercise->getResourceNode()->isPublished()
            || 0 === $this->paperManager->countExercisePapers($exercise);
    }

    /**
     * Publishes an exercise.
     *
     * @param Exercise $exercise
     *
     * @throws \LogicException if the exercise is already published
     */
    public function publish(Exercise $exercise)
    {
        if (!$exercise->wasPublishedOnce()) {
            $this->paperManager->deleteAll($exercise);
            $exercise->setPublishedOnce(true);
        }

        $exercise->getResourceNode()->setPublished(true);
        $this->om->persist($exercise);

        $this->om->flush();
    }

    /**
     * Unpublishes an exercise.
     *
     * @param Exercise $exercise
     */
    public function unpublish(Exercise $exercise)
    {
        $exercise->getResourceNode()->setPublished(false);
        $this->om->flush();
    }

    /**
     * Generates new ids for quiz entities.
     *
     * @param Exercise $exercise
     */
    private function refreshIdentifiers(Exercise $exercise)
    {
        $exercise->refreshUuid();

        foreach ($exercise->getSteps() as $step) {
            $step->refreshUuid();
            foreach ($step->getQuestions() as $item) {
                $this->itemManager->refreshIdentifiers($item);
            }
        }
    }

    /**
     * Applies an arbitrary parser on all HTML contents in the quiz definition.
     *
     * @param ContentParserInterface $contentParser
     * @param \stdClass              $quizData
     */
    public function parseContents(ContentParserInterface $contentParser, \stdClass $quizData)
    {
        if (isset($quizData->description)) {
            $quizData->description = $contentParser->parse($quizData->description);
        }

        array_walk($quizData->steps, function (\stdClass $step) use ($contentParser) {
            if (isset($step->description)) {
                $step->description = $contentParser->parse($step->description);
            }

            array_walk($step->items, function (\stdClass $item) use ($contentParser) {
                $this->itemManager->parseContents($contentParser, $item);
            });
        });
    }

    /**
     * Creates a copy of a quiz definition.
     * (aka it creates a new entity if needed and generate new IDs for quiz data).
     *
     * @param \stdClass     $srcData
     * @param Exercise|null $copyDestination - an existing Exercise entity to store the copy
     *
     * @return Exercise
     */
    public function createCopy(\stdClass $srcData, Exercise $copyDestination = null)
    {
        $copyDestination = $this->serializer->deserialize($srcData, $copyDestination, [Transfer::NO_FETCH]);
        $this->refreshIdentifiers($copyDestination);

        // Persist copy
        $this->om->persist($copyDestination);

        return $copyDestination;
    }

    public function exportPapersToCsv(Exercise $exercise)
    {
        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository('UJMExoBundle:Attempt\Paper');
        $papers = $repo->findBy([
            'exercise' => $exercise,
        ]);

        $handle = fopen('php://output', 'w+');
        $limit = 250;
        $iteration = 0;
        $papers = [];

        while (0 === $iteration || count($papers) >= $limit) {
            $papers = $repo->findBy(['exercise' => $exercise], [], $limit, $iteration * $limit);
            ++$iteration;

            /** @var Paper $paper */
            foreach ($papers as $paper) {
                $structure = json_decode($paper->getStructure());
                $totalScoreOn = $structure->parameters->totalScoreOn && floatval($structure->parameters->totalScoreOn) > 0 ? floatval($structure->parameters->totalScoreOn) : $this->paperManager->calculateTotal($paper);
                $user = $paper->getUser();
                $score = $this->paperManager->calculateScore($paper, $totalScoreOn);
                fputcsv($handle, [
                    $user && !$paper->isAnonymized() ? $user->getLastName() : '',
                    $user && !$paper->isAnonymized() ? $user->getFirstName() : '',
                    $paper->getNumber(),
                    $paper->getStart()->format('Y-m-d H:i:s'),
                    $paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : '',
                    $paper->isInterrupted() ? 'not finished' : 'finished',
                    $score !== floor($score) ? number_format($score, 2) : $score,
                    $totalScoreOn,
                ], ';');
            }

            $this->om->clear('UJM\ExoBundle\Entity\Attempt\Paper');
        }

        fclose($handle);

        return $handle;
    }

    public function exportResultsToCsv(Exercise $exercise)
    {
        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository('UJMExoBundle:Attempt\Paper');

        $titles = [['username'], ['lastname'], ['firstname'], ['start'], ['end'], ['status'], ['score'], ['total_score_on']];
        $items = [];
        $questions = [];

        //get the list of titles for the csv (the headers)
        //this is an array of array because some question types will return...
        //more than 1 title (ie clozes)
        foreach ($exercise->getSteps() as $step) {
            foreach ($step->getStepQuestions() as $stepQ) {
                $item = $stepQ->getQuestion();
                $items[$item->getId()] = $item;
                $questions[$stepQ->getQuestion()->getUuid()] = $stepQ->getQuestion();
                $itemType = $item->getInteraction();

                if ($this->definitions->has($item->getMimeType())) {
                    $definition = $this->definitions->get($item->getMimeType());
                    $titles[$item->getUuid()] = $definition->getCsvTitles($itemType);
                }
            }
        }

        $flattenedTitles = [];

        foreach ($titles as $title) {
            foreach ($title as $subTitle) {
                $flattenedTitles[] = $subTitle;
            }
        }

        $fp = fopen('php://output', 'w+');
        fputcsv($fp, $flattenedTitles, ';');

        //this is the same reason why we use an array of array here
        $repo = $this->om->getRepository('UJMExoBundle:Attempt\Paper');
        $limit = 250;
        $iteration = 0;
        $papers = [];

        while (0 === $iteration || count($papers) >= $limit) {
            $papers = $repo->findBy(['exercise' => $exercise], [], $limit, $iteration * $limit);
            ++$iteration;
            $dataPapers = [];

            foreach ($papers as $paper) {
                $structure = json_decode($paper->getStructure());
                $totalScoreOn = $structure->parameters->totalScoreOn && floatval($structure->parameters->totalScoreOn) > 0 ? floatval($structure->parameters->totalScoreOn) : $this->paperManager->calculateTotal($paper);
                $score = $this->paperManager->calculateScore($paper, $totalScoreOn);

                $answers = $paper->getAnswers();
                $csv = [];
                $user = $paper->getUser();

                if ($user) {
                    $csv['username'] = [$user->getUsername()];
                    $csv['lastname'] = [$user->getLastName()];
                    $csv['firstname'] = [$user->getFirstName()];
                } else {
                    $csv['username'] = ['none'];
                    $csv['lastname'] = ['none'];
                    $csv['firstname'] = ['none'];
                }

                $csv['start'] = [$paper->getStart()->format('Y-m-d H:i:s')];
                $csv['end'] = [$paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : ''];
                $csv['status'] = [$paper->isInterrupted() ? 'not finished' : 'finished'];
                $csv['score'] = [$score !== floor($score) ? number_format($score, 2) : $score];
                $csv['total_score_on'] = [$totalScoreOn];

                $notFound = [];
                foreach ($questions as $question) {
                    $item = $items[$question->getId()];
                    $found = false;

                    foreach ($answers as $answer) {
                        if ($answer->getQuestionId() === $question->getUuid()) {
                            if ($this->definitions->has($item->getMimeType())) {
                                $found = true;
                                $definition = $this->definitions->get($item->getMimeType());
                                $csv[$answer->getQuestionId()] = $definition->getCsvAnswers($item->getInteraction(), $answer);
                            }
                        }
                    }

                    if (!$found) {
                        $notFound[] = $question->getUuid();
                        $items[$question->getId()];
                        $itemType = $item->getInteraction();
                        $countBlank = 0;

                        if ($this->definitions->has($item->getMimeType())) {
                            $definition = $this->definitions->get($item->getMimeType());
                            $countBlank = count($definition->getCsvTitles($itemType));
                        }

                        $blankData = [];
                        for ($i = 0; $i < $countBlank; ++$i) {
                            $blankData[] = '';
                        }

                        $csv[$item->getUuid()] = $blankData;
                    }
                }

                $dataPapers[] = $csv;
            }

            $flattenedData = [];

            foreach ($dataPapers as $paper) {
                $flattenedAnswers = [];
                foreach ($paper as $paperItem) {
                    if (is_array($paperItem)) {
                        foreach ($paperItem as $paperEl) {
                            $flattenedAnswers[] = $paperEl;
                        }
                    }
                }
                $flattenedData[] = $flattenedAnswers;
            }

            $this->om->clear('UJM\ExoBundle\Entity\Attempt\Paper');

            foreach ($flattenedData as $item) {
                fputcsv($fp, $item, ';');
            }
        }

        fclose($fp);

        return $fp;
    }
}
