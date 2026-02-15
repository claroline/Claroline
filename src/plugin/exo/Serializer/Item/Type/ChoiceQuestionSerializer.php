<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Serializer\Content\ContentSerializer;

class ChoiceQuestionSerializer
{
    use SerializerTrait;

    public function __construct(
        private readonly ContentSerializer $contentSerializer
    ) {
    }

    public function getName(): string
    {
        return 'exo_question_choice';
    }

    public function serialize(ChoiceQuestion $choiceQuestion, array $options = []): array
    {
        $serialized = [
            'random' => $choiceQuestion->getShuffle(),
            'multiple' => $choiceQuestion->isMultiple(),
            'numbering' => $choiceQuestion->getNumbering(),
            'direction' => $choiceQuestion->getDirection(),
        ];

        // Serializes choices
        $choices = $this->serializeChoices($choiceQuestion, $options);

        if ($choiceQuestion->getShuffle() && in_array(Transfer::SHUFFLE_ANSWERS, $options)) {
            shuffle($choices);
        }

        $serialized['choices'] = $choices;

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $serialized['solutions'] = $this->serializeSolutions($choiceQuestion);
        }

        return $serialized;
    }

    public function deserialize(array $data, ?ChoiceQuestion $choiceQuestion = null, array $options = []): ChoiceQuestion
    {
        if (empty($choiceQuestion)) {
            $choiceQuestion = new ChoiceQuestion();
        }

        $this->sipe('multiple', 'setMultiple', $data, $choiceQuestion);
        $this->sipe('random', 'setShuffle', $data, $choiceQuestion);
        $this->sipe('numbering', 'setNumbering', $data, $choiceQuestion);
        $this->sipe('direction', 'setDirection', $data, $choiceQuestion);

        $this->deserializeChoices($choiceQuestion, $data['choices'], $data['solutions'], $options);

        return $choiceQuestion;
    }

    /**
     * Shuffles and serializes the Question choices.
     * To avoid shuffling, set `$options['randomize']` to false (e.g. we don't want shuffle for papers).
     */
    private function serializeChoices(ChoiceQuestion $choiceQuestion, array $options = []): array
    {
        return array_map(function (Choice $choice) use ($options) {
            $choiceData = $this->contentSerializer->serialize($choice, $options);
            // TODO : finish content management. For now the choice id overlaps the content ID.
            $choiceData['id'] = $choice->getUuid();

            return $choiceData;
        }, $choiceQuestion->getChoices()->toArray());
    }

    private function deserializeChoices(ChoiceQuestion $choiceQuestion, array $choices, array $solutions, array $options = []): void
    {
        $choiceEntities = $choiceQuestion->getChoices()->toArray();

        foreach ($choices as $index => $choiceData) {
            $choice = null;

            // Searches for an existing choice entity.
            foreach ($choiceEntities as $entityIndex => $entityChoice) {
                /** @var Choice $entityChoice */
                if ($entityChoice->getUuid() === $choiceData['id']) {
                    $choice = $entityChoice;
                    unset($choiceEntities[$entityIndex]);
                    break;
                }
            }

            $choice = $choice ?: new Choice();
            $choice->setUuid($choiceData['id']);
            $choice->setOrder($index);

            // Deserialize choice content
            $choice = $this->contentSerializer->deserialize($choiceData, $choice, $options);

            // Set choice score and feedback
            $choice->setScore(0);

            foreach ($solutions as $solution) {
                if ($solution['id'] === $choiceData['id']) {
                    $choice->setScore($solution['score']);

                    if (isset($solution['feedback'])) {
                        $choice->setFeedback($solution['feedback']);
                    }
                    break;
                }
            }

            if (0 < $choice->getScore()) {
                $choice->setExpected(true);
            }

            $choiceQuestion->addChoice($choice);
        }

        // Remaining choices are no longer in the Question
        foreach ($choiceEntities as $choiceToRemove) {
            $choiceQuestion->removeChoice($choiceToRemove);
        }
    }

    private function serializeSolutions(ChoiceQuestion $choiceQuestion): array
    {
        return array_map(function (Choice $choice) {
            $solutionData = [
                'id' => $choice->getUuid(),
                'score' => $choice->getScore(),
            ];

            if ($choice->getFeedback()) {
                $solutionData['feedback'] = $choice->getFeedback();
            }

            return $solutionData;
        }, $choiceQuestion->getChoices()->toArray());
    }
}
