<?php

namespace UJM\ExoBundle\Library\Attempt;

use Claroline\CoreBundle\Entity\User;
use UJM\ExoBundle\Entity\Attempt\Paper;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Item\Item;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Library\Options\Picking;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\ExerciseSerializer;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;
use UJM\ExoBundle\Serializer\StepSerializer;

/**
 * PaperGenerator creates new paper instances for attempts to exercises.
 * It takes into account the exercise and steps configuration to create the correct attempt structure.
 */
class PaperGenerator
{
    /** @var ExerciseSerializer */
    private $exerciseSerializer;

    /** @var StepSerializer */
    private $stepSerializer;

    /** @var ItemSerializer */
    private $itemSerializer;

    /**
     * PaperGenerator constructor.
     */
    public function __construct(
        ExerciseSerializer $exerciseSerializer,
        StepSerializer $stepSerializer,
        ItemSerializer $itemSerializer
    ) {
        $this->exerciseSerializer = $exerciseSerializer;
        $this->stepSerializer = $stepSerializer;
        $this->itemSerializer = $itemSerializer;
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
     * @param Paper $previousPaper
     *
     * @return array
     */
    private function generateStructure(Exercise $exercise, Paper $previousPaper = null)
    {
        // The structure of the previous paper if any
        $previousStructure = !empty($previousPaper) ? $previousPaper->getStructure(true) : null;

        // Get JSON representation of the full exercise
        $structure = $this->exerciseSerializer->serialize($exercise);
        // Pick questions for each steps and generate structure
        $structure['steps'] = $this->pickSteps($exercise, $previousStructure);

        return $structure;
    }

    private function pickSteps(Exercise $exercise, array $previousExercise = null)
    {
        switch ($exercise->getPicking()) {
            case Picking::TAGS:
                return $this->pickStepsByTags($exercise, $previousExercise);

            case Picking::STANDARD:
            default:
                if (!empty($previousExercise) && Recurrence::ALWAYS !== $exercise->getRandomPick()) {
                    // Just get the list of steps from the previous paper
                    $steps = array_map(function (array $pickedStep) use ($exercise) {
                        return $exercise->getStep($pickedStep['id']);
                    }, $previousExercise['steps']);
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
                        foreach ($previousExercise['steps'] as $stepStructure) {
                            if ($stepStructure['id'] === $step->getUuid()) {
                                $previousStructure = $stepStructure;
                                break;
                            }
                        }
                    }

                    $pickedStep = $this->stepSerializer->serialize($step);
                    $pickedStep['items'] = $this->pickItems($step, $previousStructure);
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
     * Generates steps based on the quiz configuration and a list of items.
     * In this kind of quiz all items are stored in a single step.
     *
     * @param array $previousExercise
     *
     * @return array
     */
    private function pickStepsByTags(Exercise $exercise, array $previousExercise = null)
    {
        $pickConfig = $exercise->getPick();

        // Retrieve the list of items to use
        $items = [];

        if (!empty($previousExercise) && Recurrence::ALWAYS !== $exercise->getRandomPick()) {
            // Just get the list of items from the previous paper
            foreach ($previousExercise['steps'] as $pickedStep) {
                foreach ($pickedStep['items'] as $pickedItem) {
                    $items[] = $exercise->getQuestion($pickedItem['id']);
                }
            }
        } else {
            // Get the list of items from exercise
            foreach ($exercise->getSteps() as $step) {
                $items = array_merge($items, $step->getQuestions());
            }
        }

        // Serialize items (we will automatically get items tags for filtering)
        $serializedItems = array_map(function (Item $pickedItem) {
            return $this->itemSerializer->serialize($pickedItem, [
                Transfer::SHUFFLE_ANSWERS,
                Transfer::INCLUDE_SOLUTIONS,
            ]);
        }, $items);

        $pickedItems = [];

        if (!empty($previousExercise) && Recurrence::ALWAYS !== $exercise->getRandomPick()) {
            // items are already filtered
            $pickedItems = $serializedItems;
        } else {
            // Only pick wanted tags (format : ['tagName', itemCount])
            foreach ($pickConfig['tags'] as $pickedTag) {
                $taggedItems = array_filter($serializedItems, function ($serializedItem) use ($pickedTag) {
                    return !empty($serializedItem['tags']) && in_array($pickedTag[0], $serializedItem['tags']);
                });

                // Get the correct number of items with the current tag
                // There is no error if we want more items than there are in the quiz,
                // we just stop to pick when there are no more available items
                $pickedItems = array_merge($pickedItems, static::pick($taggedItems, $pickedTag[1], true));
            }
        }

        // Shuffle items according to config
        if ((empty($previousExercise) && Recurrence::ONCE === $exercise->getRandomOrder())
            || Recurrence::ALWAYS === $exercise->getRandomOrder()) {
            shuffle($pickedItems);
        }

        // Create steps and fill it with the correct number of questions
        $pickedSteps = [];
        while (!empty($pickedItems)) {
            $pickedStep = $this->stepSerializer->serialize(new Step());
            $pickedStep['items'] = array_splice($pickedItems, 0, $pickConfig['pageSize']);
            $pickedSteps[] = $pickedStep;
        }

        return $pickedSteps;
    }

    /**
     * Pick items for a step according to the step configuration.
     *
     * @return Item[]
     */
    private function pickItems(Step $step, array $previousStep = null)
    {
        if (!empty($previousStep) && Recurrence::ALWAYS !== $step->getRandomPick()) {
            // Just get the list of question from previous step
            // We get the entities to reapply shuffle (= redo serialization with shuffle option)
            $items = array_filter(
                array_map(function (array $pickedItem) use ($step) {
                    return $step->getQuestion($pickedItem['id']);
                }, $previousStep['items']),
                function ($item) {
                    return !empty($item);
                }
            );
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
     * @param bool  $force
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
                // only one element has been picked
                $randomSelect = [$randomSelect];
            }

            // put back original collection order
            sort($randomSelect, SORT_NUMERIC);

            foreach ($randomSelect as $randomIndex) {
                $picked[] = $collection[$randomIndex];
            }
        } else {
            $picked = $collection;
        }

        return $picked;
    }
}
