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

    public function __construct(
        private readonly ItemSerializer $itemSerializer
    ) {
    }

    public function getName(): string
    {
        return 'exo_step';
    }

    public function getClass(): string
    {
        return Step::class;
    }

    public function serialize(Step $step, array $options = []): array
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
     */
    public function deserialize(array $data, Step $step = null, array $options = []): Step
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

    private function serializeParameters(Step $step): array
    {
        return [
            'duration' => $step->getDuration(),
            'maxAttempts' => $step->getMaxAttempts(),
        ];
    }

    private function deserializeParameters(Step $step, array $parameters): void
    {
        $this->sipe('maxAttempts', 'setMaxAttempts', $parameters, $step);
        $this->sipe('duration', 'setDuration', $parameters, $step);
    }

    private function serializePicking(Step $step): array
    {
        return [
            'randomOrder' => $step->getRandomOrder(),
            'randomPick' => $step->getRandomPick(),
            'pick' => $step->getPick(),
        ];
    }

    private function deserializePicking(Step $step, array $picking): void
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
     */
    public function serializeItems(Step $step, array $options = []): array
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
     */
    public function deserializeItems(Step $step, array $items = [], array $options = []): void
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
