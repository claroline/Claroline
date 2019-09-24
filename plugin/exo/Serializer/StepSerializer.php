<?php

namespace UJM\ExoBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Entity\StepItem;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Item\ItemSerializer;

/**
 * Serializer for step data.
 */
class StepSerializer
{
    use SerializerTrait;

    /**
     * @var ItemSerializer
     */
    private $itemSerializer;

    /**
     * StepSerializer constructor.
     *
     * @param ItemSerializer $itemSerializer
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
     * @return array
     */
    public function serialize(Step $step, array $options = [])
    {
        $serialized = [
            'id' => $step->getUuid(),
            'slug' => $step->getSlug(),
            'parameters' => $this->serializeParameters($step),
            'picking' => $this->serializePicking($step),
            'items' => $this->serializeItems($step, $options),
        ];

        if (!empty($step->getTitle())) {
            $serialized['title'] = $step->getTitle();
        }
        if (!empty($step->getDescription())) {
            $serialized['description'] = $step->getDescription();
        }

        return $serialized;
    }

    /**
     * Converts raw data into a Step entity.
     *
     * @param array $data
     * @param Step  $step
     * @param array $options
     *
     * @return Step
     */
    public function deserialize($data, Step $step = null, array $options = [])
    {
        $step = $step ?: new Step();

        $this->sipe('id', 'setUuid', $data, $step);
        $this->sipe('title', 'setTitle', $data, $step);
        $this->sipe('slug', 'setSlug', $data, $step);
        $this->sipe('description', 'setDescription', $data, $step);

        if (!$step->getTitle() && !$step->getSlug()) {
            $step->setSlug('step-'.$step->getOrder());
        }

        if (in_array(Transfer::REFRESH_UUID, $options)) {
            $step->refreshUuid();
        }

        if (!empty($data['parameters'])) {
            $this->deserializeParameters($step, $data['parameters']);
        }

        if (!empty($data['picking'])) {
            $this->deserializePicking($step, $data['picking']);
        }

        if (!empty($data['items'])) {
            $this->deserializeItems($step, $data['items'], $options);
        }

        return $step;
    }

    /**
     * Serializes Step parameters.
     *
     * @param Step $step
     *
     * @return array
     */
    private function serializeParameters(Step $step)
    {
        return [
            'duration' => $step->getDuration(),
            'maxAttempts' => $step->getMaxAttempts(),
        ];
    }

    /**
     * Deserializes Step parameters.
     *
     * @param Step  $step
     * @param array $parameters
     */
    private function deserializeParameters(Step $step, array $parameters)
    {
        $this->sipe('maxAttempts', 'setMaxAttempts', $parameters, $step);
        $this->sipe('duration', 'setDuration', $parameters, $step);
    }

    private function serializePicking(Step $step)
    {
        return [
            'randomOrder' => $step->getRandomOrder(),
            'randomPick' => $step->getRandomPick(),
            'pick' => $step->getPick(),
        ];
    }

    private function deserializePicking(Step $step, array $picking)
    {
        $this->sipe('randomOrder', 'setRandomOrder', $picking, $step);

        if (isset($picking['randomPick'])) {
            $step->setRandomPick($picking['randomPick']);

            if (Recurrence::ONCE === $picking['randomPick'] || Recurrence::ALWAYS === $picking['randomPick']) {
                $step->setPick($picking['pick']);
            } else {
                $step->setPick(0);
            }
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
        return array_values(array_map(function (StepItem $stepQuestion) use ($options) {
            $serialized = $this->itemSerializer->serialize($stepQuestion->getQuestion(), $options);
            $serialized['meta']['mandatory'] = $stepQuestion->isMandatory();

            return $serialized;
        }, $step->getStepQuestions()->toArray()));
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
                if ($entityStepQuestion->getQuestion()->getUuid() === $itemData['id']) {
                    $stepQuestion = $entityStepQuestion;
                    $item = $stepQuestion->getQuestion();
                    unset($stepQuestions[$entityIndex]);
                    break;
                }
            }

            $entity = $this->itemSerializer->deserialize($itemData, $item, $options);

            if (empty($stepQuestion)) {
                // Creation of a new item (we need to link it to the Step)
                $stepQuestion = $step->addQuestion($entity);
            } else {
                // Update order of the Item in the Step
                $stepQuestion->setOrder($index);
            }

            if (isset($itemData['meta']['mandatory'])) {
                $stepQuestion->setMandatory($itemData['meta']['mandatory']);
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
