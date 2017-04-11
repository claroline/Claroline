<?php

namespace UJM\ExoBundle\Serializer;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Entity\StepItem;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;

/**
 * Serializer for step data.
 *
 * @DI\Service("ujm_exo.serializer.step")
 */
class StepSerializer implements SerializerInterface
{
    /**
     * @var ItemSerializer
     */
    private $itemSerializer;

    /**
     * StepSerializer constructor.
     *
     * @param ItemSerializer $itemSerializer
     *
     * @DI\InjectParams({
     *     "itemSerializer" = @DI\Inject("ujm_exo.serializer.item")
     * })
     */
    public function __construct(ItemSerializer $itemSerializer)
    {
        $this->itemSerializer = $itemSerializer;
    }

    /**
     * Converts a Step into a JSON-encodable structure.
     *
     * @param Step  $step
     * @param array $options
     *
     * @return \stdClass
     */
    public function serialize($step, array $options = [])
    {
        $stepData = new \stdClass();
        $stepData->id = $step->getUuid();

        if (!empty($step->getTitle())) {
            $stepData->title = $step->getTitle();
        }

        if (!empty($step->getDescription())) {
            $stepData->description = $step->getDescription();
        }

        $stepData->parameters = $this->serializeParameters($step);
        $stepData->items = $this->serializeItems($step, $options);

        return $stepData;
    }

    /**
     * Converts raw data into a Step entity.
     *
     * @param \stdClass $data
     * @param Step      $step
     * @param array     $options
     *
     * @return Step
     */
    public function deserialize($data, $step = null, array $options = [])
    {
        $step = $step ?: new Step();
        $step->setUuid($data->id);

        if (isset($data->title)) {
            $step->setTitle($data->title);
        }

        if (isset($data->description)) {
            $step->setDescription($data->description);
        }

        if (!empty($data->parameters)) {
            $this->deserializeParameters($step, $data->parameters);
        }

        if (!empty($data->items)) {
            $this->deserializeItems($step, $data->items, $options);
        }

        return $step;
    }

    /**
     * Serializes Step parameters.
     *
     * @param Step $step
     *
     * @return \stdClass
     */
    private function serializeParameters(Step $step)
    {
        $parameters = new \stdClass();

        // Attempt parameters
        $parameters->randomOrder = $step->getRandomOrder();
        $parameters->randomPick = $step->getRandomPick();
        $parameters->pick = $step->getPick();
        $parameters->duration = $step->getDuration();
        $parameters->maxAttempts = $step->getMaxAttempts();

        return $parameters;
    }

    /**
     * Deserializes Step parameters.
     *
     * @param Step      $step
     * @param \stdClass $parameters
     */
    private function deserializeParameters(Step $step, \stdClass $parameters)
    {
        if (isset($parameters->randomOrder)) {
            $step->setRandomOrder($parameters->randomOrder);
        }

        if (isset($parameters->randomPick)) {
            $step->setRandomPick($parameters->randomPick);
            if (Recurrence::ONCE === $parameters->randomPick || Recurrence::ALWAYS === $parameters->randomPick) {
                $step->setPick($parameters->pick);
            } else {
                $step->setPick(0);
            }
        }

        if (isset($parameters->maxAttempts)) {
            $step->setMaxAttempts($parameters->maxAttempts);
        }

        if (isset($parameters->duration)) {
            $step->setDuration($parameters->duration);
        }
    }

    /**
     * Serializes Step items.
     * Forwards the item serialization to ItemSerializer.
     *
     * @param Step  $step
     * @param array $options
     *
     * @return array
     */
    public function serializeItems(Step $step, array $options = [])
    {
        $stepQuestions = $step->getStepQuestions()->toArray();

        return array_map(function (StepItem $stepQuestion) use ($options) {
            return $this->itemSerializer->serialize($stepQuestion->getQuestion(), $options);
        }, $stepQuestions);
    }

    /**
     * Deserializes Step items.
     * Forwards the item deserialization to ItemSerializer.
     *
     * @param Step  $step
     * @param array $items
     * @param array $options
     */
    public function deserializeItems(Step $step, array $items = [], array $options = [])
    {
        $stepQuestions = $step->getStepQuestions()->toArray();

        foreach ($items as $index => $itemData) {
            $item = null;
            $stepQuestion = null;

            // Searches for an existing item entity.
            foreach ($stepQuestions as $entityIndex => $entityStepQuestion) {
                /** @var StepItem $entityStepQuestion */
                if ($entityStepQuestion->getQuestion()->getUuid() === $itemData->id) {
                    $stepQuestion = $entityStepQuestion;
                    $item = $stepQuestion->getQuestion();
                    unset($stepQuestions[$entityIndex]);
                    break;
                }
            }

            $entity = $this->itemSerializer->deserialize($itemData, $item, $options);

            if (empty($stepQuestion)) {
                // Creation of a new item (we need to link it to the Step)
                $step->addQuestion($entity);
            } else {
                // Update order of the Item in the Step
                $stepQuestion->setOrder($index);
            }
        }

        // Remaining items are no longer in the Step
        if (0 < count($stepQuestions)) {
            foreach ($stepQuestions as $stepQuestionToRemove) {
                $step->removeStepQuestion($stepQuestionToRemove);
            }
        }
    }
}
