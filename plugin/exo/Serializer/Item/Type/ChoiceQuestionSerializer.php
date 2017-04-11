<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\ChoiceQuestion;
use UJM\ExoBundle\Entity\Misc\Choice;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;
use UJM\ExoBundle\Serializer\Content\ContentSerializer;

/**
 * @DI\Service("ujm_exo.serializer.question_choice")
 */
class ChoiceQuestionSerializer implements SerializerInterface
{
    /**
     * @var ContentSerializer
     */
    private $contentSerializer;

    /**
     * ChoiceQuestionSerializer constructor.
     *
     * @param ContentSerializer $contentSerializer
     *
     * @DI\InjectParams({
     *     "contentSerializer" = @DI\Inject("ujm_exo.serializer.content")
     * })
     */
    public function __construct(ContentSerializer $contentSerializer)
    {
        $this->contentSerializer = $contentSerializer;
    }

    /**
     * Converts a Choice question into a JSON-encodable structure.
     *
     * @param ChoiceQuestion $choiceQuestion
     * @param array          $options
     *
     * @return \stdClass
     */
    public function serialize($choiceQuestion, array $options = [])
    {
        $questionData = new \stdClass();

        $questionData->random = $choiceQuestion->getShuffle();
        $questionData->multiple = $choiceQuestion->isMultiple();

        // Serializes choices
        $choices = $this->serializeChoices($choiceQuestion, $options);
        if ($choiceQuestion->getShuffle() && in_array(Transfer::SHUFFLE_ANSWERS, $options)) {
            shuffle($choices);
        }

        $questionData->choices = $choices;

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $questionData->solutions = $this->serializeSolutions($choiceQuestion);
        }

        return $questionData;
    }

    /**
     * Converts raw data into a Choice question entity.
     *
     * @param \stdClass      $data
     * @param ChoiceQuestion $choiceQuestion
     * @param array          $options
     *
     * @return ChoiceQuestion
     */
    public function deserialize($data, $choiceQuestion = null, array $options = [])
    {
        if (empty($choiceQuestion)) {
            $choiceQuestion = new ChoiceQuestion();
        }

        $choiceQuestion->setMultiple($data->multiple);

        if (isset($data->random)) {
            $choiceQuestion->setShuffle($data->random);
        }

        $this->deserializeChoices($choiceQuestion, $data->choices, $data->solutions, $options);

        return $choiceQuestion;
    }

    /**
     * Shuffles and serializes the Question choices.
     * To avoid shuffling, set `$options['randomize']` to false (eg. we don't want shuffle for papers).
     *
     * @param ChoiceQuestion $choiceQuestion
     * @param array          $options
     *
     * @return array
     */
    private function serializeChoices(ChoiceQuestion $choiceQuestion, array $options = [])
    {
        return array_map(function (Choice $choice) use ($options) {
            $choiceData = $this->contentSerializer->serialize($choice, $options);
            // TODO : finish content management. For now the choice id overlaps the content ID.
            $choiceData->id = $choice->getUuid();

            return $choiceData;
        }, $choiceQuestion->getChoices()->toArray());
    }

    /**
     * Deserializes Question choices.
     *
     * @param ChoiceQuestion $choiceQuestion
     * @param array          $choices
     * @param array          $solutions
     * @param array          $options
     */
    private function deserializeChoices(ChoiceQuestion $choiceQuestion, array $choices, array $solutions, array $options = [])
    {
        $choiceEntities = $choiceQuestion->getChoices()->toArray();

        foreach ($choices as $index => $choiceData) {
            $choice = null;

            // Searches for an existing choice entity.
            foreach ($choiceEntities as $entityIndex => $entityChoice) {
                /** @var Choice $entityChoice */
                if ($entityChoice->getUuid() === $choiceData->id) {
                    $choice = $entityChoice;
                    unset($choiceEntities[$entityIndex]);
                    break;
                }
            }

            $choice = $choice ?: new Choice();
            $choice->setUuid($choiceData->id);

            $choice->setOrder($index);

            // Deserialize choice content
            $choice = $this->contentSerializer->deserialize($choiceData, $choice, $options);

            // Set choice score and feedback
            $choice->setScore(0);
            foreach ($solutions as $solution) {
                if ($solution->id === $choiceData->id) {
                    $choice->setScore($solution->score);
                    if (isset($solution->feedback)) {
                        $choice->setFeedback($solution->feedback);
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

    /**
     * Serializes Question solutions.
     *
     * @param ChoiceQuestion $choiceQuestion
     *
     * @return array
     */
    private function serializeSolutions(ChoiceQuestion $choiceQuestion)
    {
        return array_map(function (Choice $choice) {
            $solutionData = new \stdClass();
            $solutionData->id = $choice->getUuid();
            $solutionData->score = $choice->getScore();

            if ($choice->getFeedback()) {
                $solutionData->feedback = $choice->getFeedback();
            }

            return $solutionData;
        }, $choiceQuestion->getChoices()->toArray());
    }
}
