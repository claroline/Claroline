<?php

namespace UJM\ExoBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UJM\ExoBundle\Entity\Exercise;
use UJM\ExoBundle\Entity\Step;
use UJM\ExoBundle\Library\Options\Picking;
use UJM\ExoBundle\Library\Options\Recurrence;
use UJM\ExoBundle\Library\Options\ShowCorrectionAt;
use UJM\ExoBundle\Library\Options\Transfer;
use UJM\ExoBundle\Manager\Item\ItemManager;

/**
 * Serializer for exercise data.
 */
class ExerciseSerializer
{
    use SerializerTrait;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var StepSerializer */
    private $stepSerializer;

    /** @var ItemManager */
    private $itemManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        StepSerializer $stepSerializer,
        ItemManager $itemManager
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->stepSerializer = $stepSerializer;
        $this->itemManager = $itemManager;
    }

    public function getName()
    {
        return 'exo_exercise';
    }

    /**
     * Converts an Exercise into a JSON-encodable structure.
     */
    public function serialize(Exercise $exercise, array $options = []): array
    {
        $serialized = [
            'id' => $exercise->getUuid(),
            'title' => $exercise->getResourceNode()->getName(), // TODO : remove me. it's required by the json schema
        ];

        if (!in_array(Transfer::MINIMAL, $options)) {
            if (!empty($exercise->getDescription())) {
                $serialized['description'] = $exercise->getDescription();
            }

            $serialized['score'] = json_decode($exercise->getScoreRule(), true);
            $serialized['parameters'] = $this->serializeParameters($exercise);
            $serialized['picking'] = $this->serializePicking($exercise);
            $serialized['steps'] = $this->serializeSteps($exercise, $options);
        }

        return $serialized;
    }

    /**
     * Converts raw data into an Exercise entity.
     */
    public function deserialize(array $data, Exercise $exercise = null, array $options = []): Exercise
    {
        $exercise = $exercise ?: new Exercise();

        $this->sipe('id', 'setUuid', $data, $exercise);
        $this->sipe('description', 'setDescription', $data, $exercise);

        if (in_array(Transfer::REFRESH_UUID, $options)) {
            $exercise->refreshUuid();
        }

        if (isset($data['score'])) {
            $score = $this->sanitizeScore($data['score']);
            $exercise->setScoreRule(json_encode($score));
        }

        if (!empty($data['parameters'])) {
            $this->deserializeParameters($exercise, $data['parameters']);
        }

        if (!empty($data['picking'])) {
            $this->deserializePicking($exercise, $data['picking']);
        }

        if (!empty($data['steps'])) {
            $this->deserializeSteps($exercise, $data['steps'], $options);
        }

        return $exercise;
    }

    /**
     * Serializes Exercise parameters.
     */
    private function serializeParameters(Exercise $exercise): array
    {
        $parameters = [
            'type' => $exercise->getType(),
            'maxAttempts' => $exercise->getMaxAttempts(),
            'maxAttemptsPerDay' => $exercise->getMaxAttemptsPerDay(),
            'maxPapers' => $exercise->getMaxPapers(),
            'showFeedback' => $exercise->getShowFeedback(),
            'progressionDisplayed' => $exercise->isProgressionDisplayed(),
            'timeLimited' => $exercise->isTimeLimited(), // todo : remove me
            'duration' => $exercise->getDuration(),
            'anonymizeAttempts' => $exercise->getAnonymizeAttempts(),
            'interruptible' => $exercise->isInterruptible(),
            'numbering' => $exercise->getNumbering(),
            'questionNumbering' => $exercise->getQuestionNumbering(),
            'showTitles' => $exercise->getShowTitles(),
            'showQuestionTitles' => $exercise->getShowQuestionTitles(),
            'mandatoryQuestions' => $exercise->getMandatoryQuestions(),
            'answersEditable' => $exercise->isAnswersEditable(),
            'showOverview' => $exercise->getShowOverview(),
            'overviewStats' => $exercise->getOverviewStats(),
            'showBack' => $exercise->getShowBack(),
            'showEndConfirm' => $exercise->getShowEndConfirm(),
            'showEndPage' => $exercise->getShowEndPage(),
            'endNavigation' => $exercise->hasEndNavigation(),
            'endStats' => $exercise->getEndStats(),
            'intermediateScores' => $exercise->getIntermediateScores(),
            'showMetadata' => $exercise->isMetadataVisible(),
            'showStatistics' => $exercise->hasStatistics(),
            'allPapersStatistics' => $exercise->isAllPapersStatistics(),
            'showFullCorrection' => !$exercise->isMinimalCorrection(),
            'showScoreAt' => $exercise->getMarkMode(),
            'showCorrectionAt' => $exercise->getCorrectionMode(),
            'successMessage' => $exercise->getSuccessMessage(),
            'failureMessage' => $exercise->getFailureMessage(),
            'successScore' => $exercise->getSuccessScore(),
            'correctionDate' => $exercise->getDateCorrection() ? DateNormalizer::normalize($exercise->getDateCorrection()) : null,
            'hasExpectedAnswers' => $exercise->hasExpectedAnswers(),
        ];

        if (!empty($exercise->getEndMessage())) {
            $parameters['endMessage'] = $exercise->getEndMessage();
        }

        return $parameters;
    }

    /**
     * Deserializes Exercise parameters.
     */
    private function deserializeParameters(Exercise $exercise, array $parameters)
    {
        $this->sipe('type', 'setType', $parameters, $exercise);
        $this->sipe('maxAttempts', 'setMaxAttempts', $parameters, $exercise);
        $this->sipe('showFeedback', 'setShowFeedback', $parameters, $exercise);
        $this->sipe('timeLimited', 'setTimeLimited', $parameters, $exercise);
        $this->sipe('progressionDisplayed', 'setProgressionDisplayed', $parameters, $exercise);
        $this->sipe('duration', 'setDuration', $parameters, $exercise);
        $this->sipe('anonymizeAttempts', 'setAnonymizeAttempts', $parameters, $exercise);
        $this->sipe('interruptible', 'setInterruptible', $parameters, $exercise);
        $this->sipe('showOverview', 'setShowOverview', $parameters, $exercise);
        $this->sipe('overviewStats', 'setOverviewStats', $parameters, $exercise);
        $this->sipe('showBack', 'setShowBack', $parameters, $exercise);
        $this->sipe('showEndConfirm', 'setShowEndConfirm', $parameters, $exercise);
        $this->sipe('showEndPage', 'setShowEndPage', $parameters, $exercise);
        $this->sipe('intermediateScores', 'setIntermediateScores', $parameters, $exercise);
        $this->sipe('endMessage', 'setEndMessage', $parameters, $exercise);
        $this->sipe('endNavigation', 'setEndNavigation', $parameters, $exercise);
        $this->sipe('endStats', 'setEndStats', $parameters, $exercise);
        $this->sipe('showMetadata', 'setMetadataVisible', $parameters, $exercise);
        $this->sipe('showStatistics', 'setStatistics', $parameters, $exercise);
        $this->sipe('allPapersStatistics', 'setAllPapersStatistics', $parameters, $exercise);
        $this->sipe('numbering', 'setNumbering', $parameters, $exercise);
        $this->sipe('questionNumbering', 'setQuestionNumbering', $parameters, $exercise);
        $this->sipe('showTitles', 'setShowTitles', $parameters, $exercise);
        $this->sipe('showQuestionTitles', 'setShowQuestionTitles', $parameters, $exercise);
        $this->sipe('mandatoryQuestions', 'setMandatoryQuestions', $parameters, $exercise);
        $this->sipe('maxAttemptsPerDay', 'setMaxAttemptsPerDay', $parameters, $exercise);
        $this->sipe('maxPapers', 'setMaxPapers', $parameters, $exercise);
        $this->sipe('successMessage', 'setSuccessMessage', $parameters, $exercise);
        $this->sipe('failureMessage', 'setFailureMessage', $parameters, $exercise);
        $this->sipe('showScoreAt', 'setMarkMode', $parameters, $exercise);
        $this->sipe('answersEditable', 'setAnswersEditable', $parameters, $exercise);
        $this->sipe('hasExpectedAnswers', 'setExpectedAnswers', $parameters, $exercise);

        if (isset($parameters['showFullCorrection'])) {
            $exercise->setMinimalCorrection(!$parameters['showFullCorrection']);
        }
        $success = isset($parameters['successScore']) &&
            '' !== $parameters['successScore'] &&
            0 <= $parameters['successScore'] &&
            100 >= $parameters['successScore'] ?
            $parameters['successScore'] :
            null;
        $exercise->setSuccessScore($success);

        if (isset($parameters['showCorrectionAt'])) {
            $exercise->setCorrectionMode($parameters['showCorrectionAt']);

            $correctionDate = null;

            if (ShowCorrectionAt::AFTER_DATE === $parameters['showCorrectionAt']) {
                $correctionDate = DateNormalizer::denormalize($parameters['correctionDate']);
            }
            $exercise->setDateCorrection($correctionDate);
        }
    }

    private function serializePicking(Exercise $exercise)
    {
        $picking = [
            'type' => $exercise->getPicking(),
            'randomOrder' => $exercise->getRandomOrder(),
            'randomPick' => $exercise->getRandomPick(),
        ];

        switch ($picking['type']) {
            case Picking::TAGS:
                $tagPicking = $exercise->getPick();
                $picking['pick'] = $tagPicking['tags'];
                $picking['pageSize'] = $tagPicking['pageSize'];
                break;
            case Picking::STANDARD:
            default:
                $picking['pick'] = $exercise->getPick();
                break;
        }

        return $picking;
    }

    private function deserializePicking(Exercise $exercise, array $picking)
    {
        $this->sipe('type', 'setPicking', $picking, $exercise);
        $this->sipe('randomOrder', 'setRandomOrder', $picking, $exercise);
        $this->sipe('randomPick', 'setRandomPick', $picking, $exercise);

        switch ($picking['type']) {
            case Picking::TAGS:
                // updates tags picking params
                $exercise->setPick([
                    'tags' => $picking['pick'],
                    'pageSize' => $picking['pageSize'],
                ]);

                break;
            case Picking::STANDARD:
            default:
                // updates steps picking params
                if (isset($picking['randomPick'])) {
                    if (Recurrence::ONCE === $picking['randomPick'] || Recurrence::ALWAYS === $picking['randomPick']) {
                        $exercise->setPick($picking['pick']);
                    } else {
                        $exercise->setPick(0);
                    }
                } else {
                    $exercise->setPick(0);
                }

                break;
        }
    }

    /**
     * Serializes Exercise steps.
     * Forwards the step serialization to StepSerializer.
     */
    private function serializeSteps(Exercise $exercise, array $options = []): array
    {
        return array_values(array_map(function (Step $step) use ($options) {
            return $this->stepSerializer->serialize($step, $options);
        }, $exercise->getSteps()->toArray()));
    }

    /**
     * Deserializes Exercise steps.
     * Forwards the step deserialization to StepSerializer.
     */
    private function deserializeSteps(Exercise $exercise, array $steps = [], array $options = [])
    {
        $stepEntities = $exercise->getSteps()->toArray();

        foreach ($steps as $index => $stepData) {
            $existingStep = null;

            // Searches for an existing step entity.
            foreach ($stepEntities as $entityIndex => $entityStep) {
                /** @var Step $entityStep */
                if ($entityStep->getUuid() === $stepData['id']) {
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

    public function getCopyOptions()
    {
        return [Transfer::INCLUDE_SOLUTIONS, Transfer::NO_FETCH, Transfer::PERSIST_TAG];
    }

    /**
     * The client may send dirty data, we need to clean them before storing it in DB.
     * (duplicated from ItemSerializer).
     *
     * @param $score
     *
     * @return array
     */
    private function sanitizeScore($score)
    {
        $sanitized = ['type' => $score['type']];

        switch ($score['type']) {
            case 'sum':
                if (isset($score['total'])) {
                    $sanitized['total'] = $score['total'];
                }
                break;
            case 'fixed':
                $sanitized['success'] = $score['success'];
                $sanitized['failure'] = $score['failure'];
                break;

            case 'manual':
                $sanitized['max'] = $score['max'];
                break;

            case 'rules':
                $sanitized['noWrongChoice'] = isset($score['noWrongChoice']) ? $score['noWrongChoice'] : false;
                $sanitized['rules'] = $score['rules'];
                break;
        }

        return $sanitized;
    }
}
