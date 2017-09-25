<?php

namespace UJM\ExoBundle\Library\Attempt;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Event\GenericDataEvent;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\ExerciseSerializer;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;
use UJM\ExoBundle\Serializer\StepSerializer;

/**
 * PaperGenerator creates new paper instances for attempts to exercises.
 * It takes into account the exercise and steps configuration to create the correct attempt structure.
 *
 * @DI\Service("ujm_exo.generator.paper")
 */
class PaperGenerator
{
    /**
     * @var ExerciseSerializer
     */
    private $exerciseSerializer;

    /**
     * @var StepSerializer
     */
    private $stepSerializer;

    /**
     * @var ItemSerializer
     */
    private $itemSerializer;

    /**
     * PaperGenerator constructor.
     *
     * @DI\InjectParams({
     *     "exerciseSerializer" = @DI\Inject("ujm_exo.serializer.exercise"),
     *     "stepSerializer"     = @DI\Inject("ujm_exo.serializer.step"),
     *     "itemSerializer"     = @DI\Inject("ujm_exo.serializer.item"),
     *     "eventDispatcher"    = @DI\Inject("event_dispatcher")
     * })
     *
     * @param ExerciseSerializer $exerciseSerializer
     * @param StepSerializer     $stepSerializer
     * @param ItemSerializer     $itemSerializer
     */
    public function __construct(
        ExerciseSerializer $exerciseSerializer,
        StepSerializer $stepSerializer,
        ItemSerializer $itemSerializer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->exerciseSerializer = $exerciseSerializer;
        $this->stepSerializer = $stepSerializer;
        $this->itemSerializer = $itemSerializer;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Creates a paper for a new attempt.
     *
     * @param Exercise $exercise      - the exercise tried
     * @param User     $user          - the user who wants to pass the exercise
     * @param Paper    $previousPaper - the previous paper if one exists
     *
     * @return Paper
     */
    public function create(Exercise $exercise, User $user = null, Paper $previousPaper = null)
    {
        // Create the new Paper entity
        $paper = new Paper();
        $paper->setExercise($exercise);
        $paper->setUser($user);
        $paper->setAnonymized($exercise->getAnonymizeAttempts());

        // Get the number of the new Paper
        $paperNum = (null === $previousPaper) ? 1 : $previousPaper->getNumber() + 1;
        $paper->setNumber($paperNum);

        // Generate the structure for the new paper
        // Reuse a previous paper if exists and has not been invalidated
        $structure = $this->generateStructure(
            $exercise,
            ($previousPaper && !$previousPaper->isInvalidated()) ? $previousPaper : null
        );
        $paper->setStructure(json_encode($structure));

        return $paper;
    }

    /**
     * Generates the structure of the attempt based on Exercise and Steps parameters.
     *
     * @param Exercise $exercise
     * @param Paper    $previousPaper
     *
     * @return \stdClass
     */
    private function generateStructure(Exercise $exercise, Paper $previousPaper = null)
    {
        // The structure of the previous paper if any
        $previousStructure = !empty($previousPaper) ? json_decode($previousPaper->getStructure()) : null;

        // Get JSON representation of the full exercise
        $structure = $this->exerciseSerializer->serialize($exercise);
        // Pick questions for each steps and generate structure
        $structure->steps = $this->pickSteps($exercise, $previousStructure);

        return $structure;
    }

    private function pickSteps(Exercise $exercise, \stdClass $previousExercise = null)
    {
        if ($exercise->getRandomTag()->pageSize > 0) {
            $pageSize = $exercise->getRandomTag()->pageSize;
            $tags = $exercise->getRandomTag()->pick;
            $total = array_reduce($tags, function ($sum, $tag) {
                return $sum + (int) $tag[1];
            }, 0);
            $countSteps = ceil($total / (int) $pageSize);
            $steps = $exercise->getSteps();
            $questions = [];

            foreach ($steps as $step) {
                $questions = array_merge($questions, array_map(function ($stepItem) {
                    return $stepItem->getQuestion();
                }, $step->getStepQuestions()->toArray()));
            }

            $pickedSteps = [];
            $pickedItems = [];
            $availableItems = [];
            $preShuffledPicked = [];

            foreach ($questions as $question) {
                $availableItems[$question->getUuid()] = $this->itemSerializer->serialize(
                    $question,
                    [
                        Transfer::SHUFFLE_ANSWERS,
                        Transfer::INCLUDE_SOLUTIONS,
                    ]
                );
            }

            foreach ($tags as $tag) {
                $taggedItems = array_filter($availableItems, function ($item) use ($tag) {
                    $itemTags = [];
                    $data = ['class' => 'UJM\ExoBundle\Entity\Item\Item', 'ids' => [$item->autoId]];
                    $event = new GenericDataEvent($data);
                    $this->eventDispatcher->dispatch('claroline_retrieve_used_tags_by_class_and_ids', $event);
                    $itemTags = $event->getResponse();

                    return is_int(array_search($tag[0], $itemTags));
                });

                $tagged = static::pick($taggedItems, $tag[1], true);
                $pickTagged = [];

                foreach ($tagged as $taggedItem) {
                    $pickTagged[$taggedItem->id] = $taggedItem;
                }

                $availableItems = array_diff_key($availableItems, $pickTagged);
                $preShuffledPicked = array_merge($preShuffledPicked, $taggedItems);
            }

            shuffle($preShuffledPicked);

            foreach ($preShuffledPicked as $picked) {
                $pickedItems[$picked->id] = $picked;
            }

            for ($i = 0; $i < $countSteps; ++$i) {
                $step = new Step();
                $step->setExercise($exercise);
                $step->setTitle('step '.($i + 1));
                $pickedStep = $this->stepSerializer->serialize($step);

                $stepItems = static::pick($pickedItems, $pageSize, true);
                $indexedStepItems = [];

                foreach ($stepItems as $stepItem) {
                    $indexedStepItems[$stepItem->id] = $stepItem;
                }

                $pickedStep->items = $stepItems;
                $pickedItems = array_diff_key($pickedItems, $indexedStepItems);

                if (count($pickedStep->items) === 0) {
                    break;
                }

                $pickedSteps[] = $pickedStep;
            }

            return $pickedSteps;
        } else {
            if (!empty($previousExercise) && Recurrence::ALWAYS !== $exercise->getRandomPick()) {
                // Just get the list of steps from the previous paper
                $steps = array_map(function (\stdClass $pickedStep) use ($exercise) {
                    return $exercise->getStep($pickedStep->id);
                }, $previousExercise->steps);
            } else {
                // Pick a new set of steps
                $steps = static::pick(
                  $exercise->getSteps()->toArray(),
                  $exercise->getPick()
              );
            }

            $pickedSteps = [];
            foreach ($steps as $step) {
                $previousStructure = null;
                if ($previousExercise) {
                    foreach ($previousExercise->steps as $stepStructure) {
                        if ($stepStructure->id === $step->getUuid()) {
                            $previousStructure = $stepStructure;
                            break;
                        }
                    }
                }

                $pickedStep = $this->stepSerializer->serialize($step);
                $pickedStep->items = $this->pickItems($step, $previousStructure);
                $pickedSteps[] = $pickedStep;
            }

            // Shuffle steps according to config
            if ((empty($previousExercise) && Recurrence::ONCE === $exercise->getRandomOrder())
              || Recurrence::ALWAYS === $exercise->getRandomOrder()) {
                shuffle($pickedSteps);
            }

            return $pickedSteps;
        }
    }

    /**
     * Pick items for a step according to the step configuration.
     *
     * @param Step           $step
     * @param \stdClass|null $previousStep
     *
     * @return Item[]
     */
    private function pickItems(Step $step, \stdClass $previousStep = null)
    {
        if (!empty($previousStep) && Recurrence::ALWAYS !== $step->getRandomPick()) {
            // Just get the list of question from previous step
            // We get the entities to reapply shuffle (= redo serialization with shuffle option)
            $items = array_map(function (\stdClass $pickedItem) use ($step) {
                return $step->getQuestion($pickedItem->id);
            }, $previousStep->items);
        } else {
            // Pick a new set of questions
            $items = static::pick(
                $step->getQuestions(),
                $step->getPick()
            );
        }

        // Serialize items
        $pickedItems = array_map(function (Item $pickedItem) {
            return $this->itemSerializer->serialize($pickedItem, [
                Transfer::SHUFFLE_ANSWERS,
                Transfer::INCLUDE_SOLUTIONS,
            ]);
        }, $items);

        // Recalculate order of the items based on the configuration
        // if we don't want to keep the one from the previous paper
        if ((empty($previousStep) && Recurrence::ONCE === $step->getRandomOrder())
            || Recurrence::ALWAYS === $step->getRandomOrder()) {
            shuffle($pickedItems);
        }

        return $pickedItems;
    }

    /**
     * Picks a subset of items in an array.
     *
     * @param array $collection - the original collection
     * @param int   $count      - the number of items to pick in the collection (if 0, the whole collection is returned)
     *
     * @return array - the truncated collection
     */
    private static function pick(array $collection, $count = 0, $force = false)
    {
        if (count($collection) < $count) {
            if ($force) {
                return $collection;
            }
            throw new \LogicException("Cannot pick more elements ({$count}) than there are in the collection.");
        }

        $picked = [];
        if (0 !== $count) {
            $randomSelect = array_rand($collection, $count);
            if (is_int($randomSelect)) {
                $randomSelect = [$randomSelect];
            }
            foreach ($randomSelect as $randomIndex) {
                $picked[] = $collection[$randomIndex];
            }
        } else {
            $picked = $collection;
        }

        return $picked;
    }
}
