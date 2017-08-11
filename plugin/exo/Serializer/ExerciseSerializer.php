<?php

namespace UJM\ExoBundle\Serializer;

use JMS\DiExtraBundle\Annotation as DI;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Library\Mode\CorrectionMode;
use UJM\ExoBundle\Library\Mode\MarkMode;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Options\ShowCorrectionAt;
use UJM\ExoBundle\Library\Options\ShowScoreAt;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Library\Serializer\SerializerInterface;
use UJM\ExoBundle\Manager\Item\ItemManager;

/**
 * Serializer for exercise data.
 *
 * @DI\Service("ujm_exo.serializer.exercise")
 */
class ExerciseSerializer implements SerializerInterface
{
    /**
     * @var UserSerializer
     */
    private $userSerializer;

    /**
     * @var StepSerializer
     */
    private $stepSerializer;

    /**
     * @var ItemManager
     */
    private $itemManager;

    /**
     * ExerciseSerializer constructor.
     *
     * @param StepSerializer $stepSerializer
     * @param UserSerializer $userSerializer
     * @param ItemManager    $itemManager
     *
     * @DI\InjectParams({
     *     "userSerializer" = @DI\Inject("ujm_exo.serializer.user"),
     *     "stepSerializer" = @DI\Inject("ujm_exo.serializer.step"),
     *     "itemManager"    = @DI\Inject("ujm_exo.manager.item")
     * })
     */
    public function __construct(
        UserSerializer $userSerializer,
        StepSerializer $stepSerializer,
        ItemManager $itemManager)
    {
        $this->userSerializer = $userSerializer;
        $this->stepSerializer = $stepSerializer;
        $this->itemManager = $itemManager;
    }

    /**
     * Converts an Exercise into a JSON-encodable structure.
     *
     * @param Exercise $exercise
     * @param array    $options
     *
     * @return \stdClass
     */
    public function serialize($exercise, array $options = [])
    {
        $exerciseData = new \stdClass();
        $exerciseData->id = $exercise->getUuid();
        $exerciseData->title = $exercise->getTitle();
        $exerciseData->meta = $this->serializeMetadata($exercise, $options);

        if (!in_array(Transfer::MINIMAL, $options)) {
            if (!empty($exercise->getDescription())) {
                $exerciseData->description = $exercise->getDescription();
            }

            $exerciseData->parameters = $this->serializeParameters($exercise);
            $exerciseData->steps = $this->serializeSteps($exercise, $options);
        }

        return $exerciseData;
    }

    /**
     * Converts raw data into an Exercise entity.
     *
     * @param \stdClass $data
     * @param Exercise  $exercise
     * @param array     $options
     *
     * @return Exercise
     */
    public function deserialize($data, $exercise = null, array $options = [])
    {
        $exercise = $exercise ?: new Exercise();
        $exercise->setUuid($data->id);
        $exercise->setTitle($data->title);

        if (isset($data->description)) {
            $exercise->setDescription($data->description);
        }

        if (!empty($data->parameters)) {
            $this->deserializeParameters($exercise, $data->parameters);
        }

        if (!empty($data->steps)) {
            $this->deserializeSteps($exercise, $data->steps, $options);
        }

        return $exercise;
    }

    /**
     * Serializes Exercise metadata.
     *
     * @param Exercise $exercise
     * @param array    $options
     *
     * @return \stdClass
     */
    private function serializeMetadata(Exercise $exercise, array $options = [])
    {
        $metadata = new \stdClass();

        $node = $exercise->getResourceNode();
        if (!empty($node)) {
            $creator = $node->getCreator();
            if (!empty($creator)) {
                $metadata->authors = [
                    $this->userSerializer->serialize($creator, $options),
                ];
            }

            $metadata->created = $node->getCreationDate()->format('Y-m-d\TH:i:s');
            $metadata->updated = $node->getModificationDate()->format('Y-m-d\TH:i:s');
        }

        $metadata->published = !empty($node) ? $node->isPublished() : $exercise->wasPublishedOnce();
        $metadata->publishedOnce = $exercise->wasPublishedOnce();

        return $metadata;
    }

    /**
     * Serializes Exercise parameters.
     *
     * @param Exercise $exercise
     *
     * @return \stdClass
     */
    private function serializeParameters(Exercise $exercise)
    {
        $parameters = new \stdClass();

        $parameters->type = $exercise->getType();

        // Attempt parameters
        $parameters->randomOrder = $exercise->getRandomOrder();
        $parameters->randomPick = $exercise->getRandomPick();
        $parameters->pick = $exercise->getPick();
        $parameters->maxAttempts = $exercise->getMaxAttempts();
        $parameters->maxAttemptsPerDay = $exercise->getMaxAttemptsPerDay();
        $parameters->maxPapers = $exercise->getMaxPapers();
        $parameters->showFeedback = $exercise->getShowFeedback();
        $parameters->duration = $exercise->getDuration();
        $parameters->anonymizeAttempts = $exercise->getAnonymizeAttempts();
        $parameters->interruptible = $exercise->isInterruptible();
        $parameters->numbering = $exercise->getNumbering();
        $parameters->mandatoryQuestions = $exercise->getMandatoryQuestions();

        // Visibility parameters
        $parameters->showOverview = $exercise->getShowOverview();
        $parameters->showEndPage = $exercise->getShowEndPage();

        if (!empty($exercise->getEndMessage())) {
            $parameters->endMessage = $exercise->getEndMessage();
        }

        $parameters->showMetadata = $exercise->isMetadataVisible();
        $parameters->showStatistics = $exercise->hasStatistics();
        $parameters->showFullCorrection = !$exercise->isMinimalCorrection();

        switch ($exercise->getMarkMode()) {
            case MarkMode::AFTER_END:
                $parameters->showScoreAt = ShowScoreAt::AFTER_END;
                break;
            case MarkMode::WITH_CORRECTION:
                $parameters->showScoreAt = ShowScoreAt::WITH_CORRECTION;
                break;
            case MarkMode::NEVER:
                $parameters->showScoreAt = ShowScoreAt::NEVER;
                break;
        }

        switch ($exercise->getCorrectionMode()) {
            case CorrectionMode::AFTER_END:
                $parameters->showCorrectionAt = ShowCorrectionAt::AFTER_END;
                break;
            case CorrectionMode::AFTER_LAST_ATTEMPT:
                $parameters->showCorrectionAt = ShowCorrectionAt::AFTER_LAST_ATTEMPT;
                break;
            case CorrectionMode::AFTER_DATE:
                $parameters->showCorrectionAt = ShowCorrectionAt::AFTER_DATE;
                break;
            case CorrectionMode::NEVER:
                $parameters->showCorrectionAt = ShowCorrectionAt::NEVER;
                break;
        }

        // score of parameter
        $parameters->totalScoreOn = $exercise->getTotalScoreOn();
        // success score
        $parameters->successScore = $exercise->getSuccessScore();

        $correctionDate = $exercise->getDateCorrection();
        $parameters->correctionDate = !empty($correctionDate) ? $correctionDate->format('Y-m-d\TH:i:s') : null;

        return $parameters;
    }

    /**
     * Deserializes Exercise parameters.
     *
     * @param Exercise  $exercise
     * @param \stdClass $parameters
     */
    private function deserializeParameters(Exercise $exercise, \stdClass $parameters)
    {
        if (isset($parameters->type)) {
            $exercise->setType($parameters->type);
        }

        if (isset($parameters->randomOrder)) {
            $exercise->setRandomOrder($parameters->randomOrder);
        }

        if (isset($parameters->randomPick)) {
            $exercise->setRandomPick($parameters->randomPick);
            if (Recurrence::ONCE === $parameters->randomPick || Recurrence::ALWAYS === $parameters->randomPick) {
                $exercise->setPick($parameters->pick);
            } else {
                $exercise->setPick(0);
            }
        }

        if (isset($parameters->maxAttempts)) {
            $exercise->setMaxAttempts($parameters->maxAttempts);
        }

        if (isset($parameters->showFeedback)) {
            $exercise->setShowFeedback($parameters->showFeedback);
        }

        if (isset($parameters->duration)) {
            $exercise->setDuration($parameters->duration);
        }

        if (isset($parameters->anonymizeAttempts)) {
            $exercise->setAnonymizeAttempts($parameters->anonymizeAttempts);
        }

        if (isset($parameters->interruptible)) {
            $exercise->setInterruptible($parameters->interruptible);
        }

        if (isset($parameters->showOverview)) {
            $exercise->setShowOverview($parameters->showOverview);
        }

        if (isset($parameters->showEndPage)) {
            $exercise->setShowEndPage($parameters->showEndPage);
        }

        if (isset($parameters->endMessage)) {
            $exercise->setEndMessage($parameters->endMessage);
        }

        if (isset($parameters->showMetadata)) {
            $exercise->setMetadataVisible($parameters->showMetadata);
        }

        if (isset($parameters->showStatistics)) {
            $exercise->setStatistics($parameters->showStatistics);
        }

        if (isset($parameters->showFullCorrection)) {
            $exercise->setMinimalCorrection(!$parameters->showFullCorrection);
        }

        if (isset($parameters->numbering)) {
            $exercise->setNumbering($parameters->numbering);
        }

        if (isset($parameters->mandatoryQuestions)) {
            $exercise->setMandatoryQuestions($parameters->mandatoryQuestions);
        }

        if (isset($parameters->maxAttemptsPerDay)) {
            $exercise->setMaxAttemptsPerDay($parameters->maxAttemptsPerDay);
        }

        if (isset($parameters->maxPapers)) {
            $exercise->setMaxPapers($parameters->maxPapers);
        }

        if (isset($parameters->showScoreAt)) {
            switch ($parameters->showScoreAt) {
                case ShowScoreAt::AFTER_END:
                    $exercise->setMarkMode(MarkMode::AFTER_END);
                    break;
                case ShowScoreAt::WITH_CORRECTION:
                    $exercise->setMarkMode(MarkMode::WITH_CORRECTION);
                    break;
                case ShowScoreAt::NEVER:
                    $exercise->setMarkMode(MarkMode::NEVER);
                    break;
            }
        }

        if (isset($parameters->totalScoreOn)) {
            $exercise->setTotalScoreOn($parameters->totalScoreOn);
        }
        $success = isset($parameters->successScore) &&
            $parameters->successScore !== '' &&
            $parameters->successScore >= 0 &&
            $parameters->successScore <= 100 ?
            $parameters->successScore :
            null;
        $exercise->setSuccessScore($success);

        if (isset($parameters->showCorrectionAt)) {
            $correctionDate = null;
            switch ($parameters->showCorrectionAt) {
                case ShowCorrectionAt::AFTER_END:
                    $exercise->setCorrectionMode(CorrectionMode::AFTER_END);
                    break;
                case ShowCorrectionAt::AFTER_LAST_ATTEMPT:
                    $exercise->setCorrectionMode(CorrectionMode::AFTER_LAST_ATTEMPT);
                    break;
                case ShowCorrectionAt::AFTER_DATE:
                case ShowCorrectionAt::AFTER_DATE:
                    $exercise->setCorrectionMode(CorrectionMode::AFTER_DATE);
                    $correctionDate = \DateTime::createFromFormat('Y-m-d\TH:i:s', $parameters->correctionDate);
                    break;
                case ShowCorrectionAt::NEVER:
                    $exercise->setCorrectionMode(CorrectionMode::NEVER);
                    break;
            }

            $exercise->setDateCorrection($correctionDate);
        }
    }

    /**
     * Serializes Exercise steps.
     * Forwards the step serialization to StepSerializer.
     *
     * @param Exercise $exercise
     * @param array    $options
     *
     * @return array
     */
    private function serializeSteps(Exercise $exercise, array $options = [])
    {
        $steps = $exercise->getSteps()->toArray();

        return array_map(function (Step $step) use ($options) {
            return $this->stepSerializer->serialize($step, $options);
        }, $steps);
    }

    /**
     * Deserializes Exercise steps.
     * Forwards the step deserialization to StepSerializer.
     *
     * @param Exercise $exercise
     * @param array    $steps
     * @param array    $options
     */
    private function deserializeSteps(Exercise $exercise, array $steps = [], array $options = [])
    {
        $stepEntities = $exercise->getSteps()->toArray();

        foreach ($steps as $index => $stepData) {
            $existingStep = null;

            // Searches for an existing step entity.
            foreach ($stepEntities as $entityIndex => $entityStep) {
                /** @var Step $entityStep */
                if ($entityStep->getUuid() === $stepData->id) {
                    $existingStep = $entityStep;
                    unset($stepEntities[$entityIndex]);
                    break;
                }
            }

            $step = $this->stepSerializer->deserialize($stepData, $existingStep, $options);
            // Set order in Exercise
            $step->setOrder($index);

            if (empty($existingStep)) {
                // Creation of a new step (we need to link it to the Exercise)
                $exercise->addStep($step);
            }
        }

        // Remaining steps are no longer in the Exercise
        if (0 < count($stepEntities)) {
            /** @var Step $stepToRemove */
            foreach ($stepEntities as $stepToRemove) {
                $exercise->removeStep($stepToRemove);
                $stepQuestions = $stepToRemove->getStepQuestions()->toArray();

                foreach ($stepQuestions as $stepQuestionToRemove) {
                    $stepToRemove->removeStepQuestion($stepQuestionToRemove);
                }
            }
        }
    }
}
