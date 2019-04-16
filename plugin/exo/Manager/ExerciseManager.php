<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Event\Log\LogExerciseUpdateEvent;
use UJM\ExoBundle\Library\Item\Definition\AnswerableItemDefinitionInterface;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\ExerciseRepository;
use UJM\ExoBundle\Repository\PaperRepository;
use UJM\ExoBundle\Serializer\ExerciseSerializer;
use UJM\ExoBundle\Validator\JsonSchema\ExerciseValidator;

/**
 * @DI\Service("ujm_exo.manager.exercise")
 */
class ExerciseManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ExerciseRepository */
    private $repository;

    /** @var ExerciseValidator */
    private $validator;

    /** @var ExerciseSerializer */
    private $serializer;

    /** @var ResourceManager $resourceManager */
    private $resourceManager;

    /** @var ItemManager */
    private $itemManager;

    /** @var PaperManager */
    private $paperManager;

    /** @var ClaroUtilities */
    private $utils;

    /** @var ItemDefinitionsCollection */
    private $definitions;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * ExerciseManager constructor.
     *
     * @DI\InjectParams({
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "validator"       = @DI\Inject("ujm_exo.validator.exercise"),
     *     "serializer"      = @DI\Inject("ujm_exo.serializer.exercise"),
     *     "resourceManager" = @DI\Inject("claroline.manager.resource_manager"),
     *     "itemManager"     = @DI\Inject("ujm_exo.manager.item"),
     *     "paperManager"    = @DI\Inject("ujm_exo.manager.paper"),
     *     "utils"           = @DI\Inject("claroline.utilities.misc"),
     *     "definitions"     = @DI\Inject("ujm_exo.collection.item_definitions"),
     *     "eventDispatcher" = @DI\Inject("event_dispatcher")
     * })
     *
     * @param ObjectManager             $om
     * @param ExerciseValidator         $validator
     * @param ExerciseSerializer        $serializer
     * @param ResourceManager           $resourceManager
     * @param ItemManager               $itemManager
     * @param PaperManager              $paperManager
     * @param ItemDefinitionsCollection $definitions
     * @param ClaroUtilities            $utils
     * @param EventDispatcherInterface  $eventDispatcher
     */
    public function __construct(
        ObjectManager $om,
        ExerciseValidator $validator,
        ExerciseSerializer $serializer,
        ResourceManager $resourceManager,
        ItemManager $itemManager,
        PaperManager $paperManager,
        ClaroUtilities $utils,
        ItemDefinitionsCollection $definitions,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->om = $om;
        $this->repository = $this->om->getRepository('UJMExoBundle:Exercise');
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->resourceManager = $resourceManager;
        $this->itemManager = $itemManager;
        $this->paperManager = $paperManager;
        $this->definitions = $definitions;
        $this->utils = $utils;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Validates and creates a new Exercise from raw data.
     *
     * @param array $data
     *
     * @return Exercise
     *
     * @throws InvalidDataException
     */
    public function create(array $data)
    {
        return $this->update(new Exercise(), $data);
    }

    /**
     * Validates and updates an Exercise entity with raw data.
     *
     * @param Exercise $exercise
     * @param array    $data
     *
     * @return Exercise
     *
     * @throws InvalidDataException
     */
    public function update(Exercise $exercise, array $data)
    {
        // Validate received data
        $errors = $this->validator->validate($data, [Validation::REQUIRE_SOLUTIONS]);

        if (count($errors) > 0) {
            throw new InvalidDataException('Exercise is not valid', $errors);
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

        // Log exercise update
        $event = new LogExerciseUpdateEvent($exercise, (array) $this->serializer->serialize($exercise));
        $this->eventDispatcher->dispatch('log', $event);

        return $exercise;
    }

    /**
     * Serializes an Exercise.
     *
     * @param Exercise $exercise
     * @param array    $options
     *
     * @return array
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
        return !$exercise->getResourceNode()->isPublished() || 0 === $this->paperManager->countExercisePapers($exercise);
    }

    /**
     * Creates a copy of a quiz definition.
     * (aka it creates a new entity if needed and generate new IDs for quiz data).
     *
     * @param array         $srcData
     * @param Exercise|null $copyDestination - an existing Exercise entity to store the copy
     *
     * @return Exercise
     */
    public function createCopy(array $srcData, Exercise $copyDestination = null)
    {
        $copyDestination = $this->serializer->deserialize($srcData, $copyDestination, [
            Transfer::NO_FETCH,
            Transfer::PERSIST_TAG,
            Transfer::REFRESH_UUID,
        ]);

        // Persist copy
        $this->om->persist($copyDestination);

        return $copyDestination;
    }

    public function export(Exercise $exercise)
    {
        $data = $this->serializer->serialize(
            $exercise,
            [Transfer::INCLUDE_SOLUTIONS, Transfer::INCLUDE_ADMIN_META]
        );
        $filename = tempnam($exercise->getResourceNode()->getName(), '');
        file_put_contents($filename, json_encode($data), FILE_APPEND);

        return $filename;
    }

    public function import(array $data, $workspace, $owner)
    {
        $exercise = new Exercise();
        $exercise->setName($data['title']);
        // Create entities from import data
        $exercise = $this->createCopy($data, $exercise);
        $parent = $this->resourceManager->getWorkspaceRoot($workspace);

        $node = $this->resourceManager->create(
            $exercise,
            $this->resourceManager->getResourceTypeByName('ujm_exercise'),
            $owner,
            $workspace,
            $parent
        );

        return $node;
    }

    public function exportPapersToCsv(Exercise $exercise)
    {
        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository('UJMExoBundle:Attempt\Paper');

        $handle = fopen('php://output', 'w+');
        $limit = 250;
        $iteration = 0;
        $papers = [];

        while (0 === $iteration || count($papers) >= $limit) {
            $papers = $repo->findBy(['exercise' => $exercise], [], $limit, $iteration * $limit);
            ++$iteration;

            /** @var Paper $paper */
            foreach ($papers as $paper) {
                $structure = json_decode($paper->getStructure(), true);
                $totalScoreOn = $structure['parameters']['totalScoreOn'] && floatval($structure['parameters']['totalScoreOn']) > 0 ?
                    floatval($structure['parameters']['totalScoreOn']) :
                    $this->paperManager->calculateTotal($paper);
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

            $this->om->clear(Paper::class);
        }

        fclose($handle);

        return $handle;
    }

    public function exportResultsToCsv(Exercise $exercise, $output = null)
    {
        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository('UJMExoBundle:Attempt\Paper');

        $titles = [['username'], ['lastname'], ['firstname'], ['start'], ['end'], ['status'], ['score'], ['total_score_on']];
        $items = [];
        $questions = [];

        //get the list of titles for the csv (the headers)
        //this is an array of array because some question types will return...
        //more than 1 title (ie cloze)
        foreach ($exercise->getSteps() as $step) {
            foreach ($step->getStepQuestions() as $stepQ) {
                $item = $stepQ->getQuestion();
                $items[$item->getId()] = $item;
                $questions[$stepQ->getQuestion()->getUuid()] = $stepQ->getQuestion();
                $itemType = $item->getInteraction();

                if ($this->definitions->has($item->getMimeType())) {
                    /** @var AnswerableItemDefinitionInterface $definition */
                    $definition = $this->definitions->get($item->getMimeType());
                    $subtitles = $definition->getCsvTitles($itemType);

                    // FIXME
                    if ('application/x.cloze+json' === $item->getMimeType()) {
                        $qText = $item->getTitle();

                        if (empty($qText)) {
                            $qText = $item->getContent();
                        }
                        foreach ($subtitles as &$holeTitle) {
                            $holeTitle = $qText.': '.$holeTitle;
                        }
                    }
                    $titles[$item->getUuid()] = $subtitles;
                }
            }
        }

        $flattenedTitles = [];

        foreach ($titles as $title) {
            foreach ($title as $subTitle) {
                $flattenedTitles[] = $this->utils->html2Csv($subTitle);
            }
        }

        if (null === $output) {
            $output = 'php://output';
        }
        $fp = fopen($output, 'w+');
        fputcsv($fp, [$exercise->getResourceNode()->getName()], ';');
        fputcsv($fp, $flattenedTitles, ';');

        //this is the same reason why we use an array of array here
        $limit = 250;
        $iteration = 0;
        $papers = [];

        while (0 === $iteration || count($papers) >= $limit) {
            $papers = $repo->findBy(['exercise' => $exercise], [], $limit, $iteration * $limit);
            ++$iteration;
            $dataPapers = [];

            /** @var Paper $paper */
            foreach ($papers as $paper) {
                $structure = json_decode($paper->getStructure(), true);
                $totalScoreOn = $structure['parameters']['totalScoreOn'] && floatval($structure['parameters']['totalScoreOn']) > 0 ?
                    floatval($structure['parameters']['totalScoreOn']) :
                    $this->paperManager->calculateTotal($paper);
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

                                /** @var AnswerableItemDefinitionInterface $definition */
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
                            $flattenedAnswers[] = $this->utils->html2Csv($paperEl, true);
                        }
                    }
                }
                $flattenedData[] = $flattenedAnswers;
            }

            $this->om->clear(Paper::class);

            foreach ($flattenedData as $item) {
                fputcsv($fp, $item, ';');
            }
        }

        fclose($fp);

        return $fp;
    }
}
