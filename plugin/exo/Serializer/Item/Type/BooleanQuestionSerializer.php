<?php

namespace UJM\ExoBundle\Serializer\Item\Type;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\ItemType\BooleanQuestion;
use UJM\ExoBundle\Entity\Misc\BooleanChoice;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;
use UJM\ExoBundle\Serializer\Content\ContentSerializer;

/**
 * @DI\Service("ujm_exo.serializer.question_boolean")
 */
class BooleanQuestionSerializer implements SerializerInterface
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
     * Converts a Boolean question into a JSON-encodable structure.
     *
     * @param BooleanQuestion $question
     * @param array           $options
     *
     * @return \stdClass
     */
    public function serialize($question, array $options = [])
    {
        $questionData = new \stdClass();

        // Serializes choices
        $choices = $this->serializeChoices($question, $options);
        if (in_array(Transfer::SHUFFLE_ANSWERS, $options)) {
            shuffle($choices);
        }

        $questionData->choices = $choices;

        if (in_array(Transfer::INCLUDE_SOLUTIONS, $options)) {
            $questionData->solutions = $this->serializeSolutions($question);
        }

        return $questionData;
    }

    /**
     * Converts raw data into a Boolean question entity.
     *
     * @param \stdClass       $data
     * @param BooleanQuestion $question
     * @param array           $options
     *
     * @return BooleanQuestion
     */
    public function deserialize($data, $question = null, array $options = [])
    {
        if (empty($question)) {
            $question = new BooleanQuestion();
        }

        $this->deserializeChoices($question, $data->choices, $data->solutions, $options);

        return $question;
    }

    /**
     * Serializes the Question choices.
     *
     * @param BooleanQuestion $question
     * @param array           $options
     *
     * @return array
     */
    private function serializeChoices(BooleanQuestion $question, array $options = [])
    {
        return array_map(function (BooleanChoice $choice) use ($options) {
            $choiceData = $this->contentSerializer->serialize($choice, $options);
            $choiceData->id = $choice->getUuid();

            return $choiceData;
        }, $question->getChoices()->toArray());
    }

    /**
     * Deserializes Question choices.
     *
     * @param BooleanQuestion $question
     * @param array           $choices
     * @param array           $solutions
     * @param array           $options
     */
    private function deserializeChoices(BooleanQuestion $question, array $choices, array $solutions, array $options = [])
    {
        $choiceEntities = $question->getChoices()->toArray();

        foreach ($choices as $choiceData) {
            $choice = null;

            // Searches for an existing choice entity.
            foreach ($choiceEntities as $entityIndex => $entityChoice) {
                /** @var BooleanChoice $entityChoice */
                if ($entityChoice->getUuid() === $choiceData->id) {
                    $choice = $entityChoice;
                    unset($choiceEntities[$entityIndex]);
                    break;
                }
            }

            $choice = $choice ?: new BooleanChoice();
            $choice->setUuid($choiceData->id);

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

            $question->addChoice($choice);
        }

        // Remaining choices are no longer in the Question
        foreach ($choiceEntities as $choiceToRemove) {
            $question->removeChoice($choiceToRemove);
        }
    }

    /**
     * Serializes Question solutions.
     *
     * @param BooleanQuestion $question
     *
     * @return array
     */
    private function serializeSolutions(BooleanQuestion $question)
    {
        return array_map(function (BooleanChoice $choice) {
            $solutionData = new \stdClass();
            $solutionData->id = $choice->getUuid();
            $solutionData->score = $choice->getScore();

            if ($choice->getFeedback()) {
                $solutionData->feedback = $choice->getFeedback();
            }

            return $solutionData;
        }, $question->getChoices()->toArray());
    }
}
