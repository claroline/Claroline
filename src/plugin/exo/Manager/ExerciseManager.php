<?php

namespace UJM\ExoBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Utilities\ClaroUtilities;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Validator\Exception\InvalidDataException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Event\Log\LogExerciseUpdateEvent;
use UJM\ExoBundle\Library\Item\Definition\AnswerableItemDefinitionInterface;
use UJM\ExoBundle\Library\Item\ItemDefinitionsCollection;
use UJM\ExoBundle\Library\Options\ExerciseType;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Options\Validation;
use UJM\ExoBundle\Manager\Attempt\PaperManager;
use UJM\ExoBundle\Manager\Item\ItemManager;
use UJM\ExoBundle\Repository\ExerciseRepository;
use UJM\ExoBundle\Repository\PaperRepository;
use UJM\ExoBundle\Serializer\ExerciseSerializer;
use UJM\ExoBundle\Validator\JsonSchema\ExerciseValidator;

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

    /** @var ResourceManager */
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
        $this->repository = $this->om->getRepository(Exercise::class);
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
     * Validates and updates an Exercise entity with raw data.
     *
     * @return Exercise
     *
     * @throws InvalidDataException
     */
    public function update(Exercise $exercise, array $data)
    {
        // Validate received data
        $validationOptions = [];
        $dataToValidate = $this->removeUnexpectedSolutions($data);

        if ($exercise->hasExpectedAnswers()) {
            $validationOptions[] = Validation::REQUIRE_SOLUTIONS;
        }
        $errors = $this->validator->validate($dataToValidate, $validationOptions);

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
        $this->eventDispatcher->dispatch($event, 'log');

        return $exercise;
    }

    /**
     * Serializes an Exercise.
     *
     * @return array
     */
    public function serialize(Exercise $exercise, array $options = [])
    {
        return $this->serializer->serialize($exercise, $options);
    }

    /**
     * Checks if an Exercise can be deleted.
     * The exercise needs to be unpublished or have no paper to be safely removed.
     *
     * @return bool
     */
    public function isDeletable(Exercise $exercise)
    {
        return ExerciseType::CERTIFICATION !== $exercise->getType()
            || !$exercise->getResourceNode()->isPublished()
            || 0 === $this->paperManager->countExercisePapers($exercise);
    }

    public function exportPapersToCsv(Exercise $exercise)
    {
        /** @var PaperRepository $repo */
        $repo = $this->om->getRepository(Paper::class);

        $handle = fopen('php://output', 'w+');
        $limit = 250;
        $iteration = 0;
        $papers = [];

        while (0 === $iteration || count($papers) >= $limit) {
            $papers = $repo->findBy(['exercise' => $exercise], [], $limit, $iteration * $limit);
            ++$iteration;

            /** @var Paper $paper */
            foreach ($papers as $paper) {
                $user = $paper->getUser();
                // maybe use stored score to speed up things
                // problem is we don't have it for non finished papers
                $score = $this->paperManager->calculateScore($paper);

                fputcsv($handle, [
                    $user && !$paper->isAnonymized() ? $user->getLastName() : '',
                    $user && !$paper->isAnonymized() ? $user->getFirstName() : '',
                    $paper->getNumber(),
                    $paper->getStart()->format('Y-m-d H:i:s'),
                    $paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : '',
                    $paper->isInterrupted() ? 'not finished' : 'finished',
                    $score !== floor($score) ? number_format($score, 2) : $score,
                    $paper->getTotal(),
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
        $repo = $this->om->getRepository(Paper::class);

        $titles = [['username'], ['lastname'], ['firstname'], ['start'], ['end'], ['status'], ['score'], ['total_score_on']];
        $items = [];

        //get the list of titles for the csv (the headers)
        //this is an array of array because some question types will return...
        //more than 1 title (ie cloze)
        foreach ($exercise->getSteps() as $step) {
            foreach ($step->getStepQuestions() as $stepQ) {
                $item = $stepQ->getQuestion();

                // only grab supported item types
                if ($this->definitions->has($item->getMimeType())) {
                    $items[$item->getUuid()] = $item;

                    /** @var AnswerableItemDefinitionInterface $definition */
                    $definition = $this->definitions->get($item->getMimeType());

                    // generate columns for item
                    $titles[$item->getUuid()] = $definition->getCsvTitles($item->getInteraction());
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
                // maybe use stored score to speed up things
                // problem is we don't have it for non finished papers
                $score = $this->paperManager->calculateScore($paper);

                $answers = $paper->getAnswers();
                $csv = [];
                $user = $paper->getUser();

                if ($user) {
                    $csv['username'] = [$paper->isAnonymized() ? '' : $user->getUsername()];
                    $csv['lastname'] = [$paper->isAnonymized() ? '' : $user->getLastName()];
                    $csv['firstname'] = [$paper->isAnonymized() ? '' : $user->getFirstName()];
                } else {
                    $csv['username'] = ['none'];
                    $csv['lastname'] = ['none'];
                    $csv['firstname'] = ['none'];
                }

                $csv['start'] = [$paper->getStart()->format('Y-m-d H:i:s')];
                $csv['end'] = [$paper->getEnd() ? $paper->getEnd()->format('Y-m-d H:i:s') : ''];
                $csv['status'] = [$paper->isInterrupted() ? 'not finished' : 'finished'];
                $csv['score'] = [$score !== floor($score) ? number_format($score, 2) : $score];
                $csv['total_score_on'] = [$paper->getTotal()];

                foreach ($items as $item) {
                    /** @var AnswerableItemDefinitionInterface $itemDefinition */
                    $itemDefinition = $this->definitions->get($item->getMimeType());

                    $found = false;
                    // get question parameters from paper
                    $itemData = $paper->getQuestion($item->getUuid());
                    if (!empty($itemData)) {
                        // get item entities
                        $paperItem = $this->itemManager->deserialize($itemData, null, [Transfer::NO_FETCH]);

                        foreach ($answers as $answer) {
                            if ($answer->getQuestionId() === $item->getUuid()) {
                                $found = true;
                                $csv[$answer->getQuestionId()] = $itemDefinition->getCsvAnswers($paperItem->getInteraction(), $answer);
                            }
                        }
                    }

                    // question has no answer, we need to add place holders
                    if (!$found) {
                        $countBlank = count($itemDefinition->getCsvTitles($item->getInteraction()));
                        $csv[$item->getUuid()] = array_pad([], $countBlank, '');
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

    private function removeUnexpectedSolutions($data)
    {
        $newData = $data;

        if (isset($newData['steps'])) {
            foreach ($newData['steps'] as $stepIdx => $step) {
                if (isset($step['items'])) {
                    foreach ($step['items'] as $itemIdx => $item) {
                        if (isset($item['solutions']) && isset($item['hasExpectedAnswers']) && !$item['hasExpectedAnswers']) {
                            unset($newData['steps'][$stepIdx]['items'][$itemIdx]['solutions']);
                        }
                    }
                }
            }
        }

        return $newData;
    }
}
