<?php

namespace UJM\ExoBundle\Library\Attempt;

use Claroline\CoreBundle\Entity\User;
use JMS\DiExtraBundle\Annotation as DI;
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
     *     "stepSerializer" = @DI\Inject("ujm_exo.serializer.step"),
     *     "itemSerializer" = @DI\Inject("ujm_exo.serializer.item")
     * })
     *
     * @param ExerciseSerializer $exerciseSerializer
     * @param StepSerializer     $stepSerializer
     * @param ItemSerializer     $itemSerializer
     */
    public function __construct(
        ExerciseSerializer $exerciseSerializer,
        StepSerializer $stepSerializer,
        ItemSerializer $itemSerializer)
    {
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
    private static function pick(array $collection, $count = 0)
    {
        if (count($collection) < $count) {
            throw new \LogicException("Cannot pick more elements ({$count}) than there are in the collection.");
        }

        $picked = [];
        if (0 !== $count) {
            $randomSelect = array_rand($collection, $count);
            foreach ($randomSelect as $randomIndex) {
                $picked[] = $collection[$randomIndex];
            }
        } else {
            $picked = $collection;
        }

        return $picked;
    }
}
