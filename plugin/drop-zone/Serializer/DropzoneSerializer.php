<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\CoreBundle\API\Serializer\SerializerTrait;
use Claroline\CoreBundle\Library\Normalizer\DateNormalizer;
use Claroline\DropZoneBundle\Entity\Criterion;
use Claroline\DropZoneBundle\Entity\Dropzone;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.serializer.dropzone")
 * @DI\Tag("claroline.serializer")
 */
class DropzoneSerializer
{
    use SerializerTrait;

    /** @var CriterionSerializer */
    private $criterionSerializer;

    /**
     * DropzoneSerializer constructor.
     *
     * @DI\InjectParams({
     *     "criterionSerializer" = @DI\Inject("claroline.serializer.dropzone.criterion")
     * })
     *
     * @param CriterionSerializer $criterionSerializer
     */
    public function __construct(CriterionSerializer $criterionSerializer)
    {
        $this->criterionSerializer = $criterionSerializer;
    }

    /**
     * @param Dropzone $dropzone
     *
     * @return array
     */
    public function serialize(Dropzone $dropzone)
    {
        return [
            'id' => $dropzone->getUuid(),
            'instruction' => $dropzone->getInstruction(),
            'parameters' => $this->serializeParameters($dropzone),
            'display' => $this->serializeDisplay($dropzone),
            'planning' => $this->serializePlanning($dropzone),
            'notifications' => $this->serializeNotifications($dropzone),
        ];
    }

    /**
     * @param array    $data
     * @param Dropzone $dropzone
     *
     * @return Dropzone
     */
    public function deserialize($data, Dropzone $dropzone)
    {
        $dropzone->setInstruction($data['instruction']);

        if (isset($data['parameters'])) {
            $this->deserializeParameters($data['parameters'], $dropzone);
        }

        if (isset($data['display'])) {
            if (isset($data['display']['correctionInstruction'])) {
                $dropzone->setCorrectionInstruction($data['display']['correctionInstruction']);
            }
            if (isset($data['display']['successMessage'])) {
                $dropzone->setSuccessMessage($data['display']['successMessage']);
            }
            if (isset($data['display']['failMessage'])) {
                $dropzone->setFailMessage($data['display']['failMessage']);
            }
            if (isset($data['display']['showScore'])) {
                $dropzone->setDisplayNotationToLearners($data['display']['showScore']);
            }
            if (isset($data['display']['showFeedback'])) {
                $dropzone->setDisplayNotationMessageToLearners($data['display']['showFeedback']);
            }
            if (isset($data['display']['displayCorrectionsToLearners'])) {
                $dropzone->setDisplayCorrectionsToLearners($data['display']['displayCorrectionsToLearners']);
            }
        }

        if (isset($data['planning'])) {
            $this->deserializePlanning($data['planning'], $dropzone);
        }

        if (isset($data['notifications'])) {
            $notifyOnDrop = isset($data['notifications']['enabled']) &&
                $data['notifications']['enabled'] &&
                isset($data['notifications']['actions']) &&
                is_array($data['notifications']['actions']) &&
                in_array('drop', $data['notifications']['actions']);
            $dropzone->setNotifyOnDrop($notifyOnDrop);
        }

        return $dropzone;
    }

    private function serializeParameters(Dropzone $dropzone)
    {
        return [
            'dropType' => $dropzone->getDropType(),
            'reviewType' => $dropzone->isPeerReview() ? 'peer' : 'manager',
            'documents' => $dropzone->getAllowedDocuments(),
            'expectedCorrectionTotal' => $dropzone->getExpectedCorrectionTotal(),
            'scoreMax' => $dropzone->getScoreMax(),
            'scoreToPass' => $dropzone->getScoreToPass(),
            'commentInCorrectionEnabled' => $dropzone->isCommentInCorrectionEnabled(),
            'commentInCorrectionForced' => $dropzone->isCommentInCorrectionForced(),
            'correctionDenialEnabled' => $dropzone->isCorrectionDenialEnabled(),
            'criteriaEnabled' => $dropzone->isCriteriaEnabled(),
            'criteria' => $this->serializeCriteria($dropzone),
            'criteriaTotal' => $dropzone->getCriteriaTotal(),
            'autoCloseDropsAtDropEndDate' => $dropzone->getAutoCloseDropsAtDropEndDate(),
        ];
    }

    private function deserializeParameters(array $parametersData, Dropzone $dropzone)
    {
        $dropzone->setPeerReview('peer' === $parametersData['reviewType']);

        if (isset($parametersData['dropType'])) {
            $dropzone->setDropType($parametersData['dropType']);
        }

        $dropzone->setAllowedDocuments($parametersData['documents']);

        if (isset($parametersData['expectedCorrectionTotal'])) {
            $dropzone->setExpectedCorrectionTotal($parametersData['expectedCorrectionTotal']);
        }
        if (isset($parametersData['scoreMax'])) {
            $dropzone->setScoreMax($parametersData['scoreMax']);
        }
        if (isset($parametersData['scoreToPass'])) {
            $dropzone->setScoreToPass($parametersData['scoreToPass']);
        }
        if (isset($parametersData['commentInCorrectionEnabled'])) {
            $dropzone->setCommentInCorrectionEnabled($parametersData['commentInCorrectionEnabled']);
        }
        if (isset($parametersData['commentInCorrectionForced'])) {
            $dropzone->setCommentInCorrectionForced($parametersData['commentInCorrectionForced']);
        }
        if (isset($parametersData['correctionDenialEnabled'])) {
            $dropzone->setCorrectionDenialEnabled($parametersData['correctionDenialEnabled']);
        }
        if (isset($parametersData['criteriaEnabled'])) {
            $dropzone->setCriteriaEnabled($parametersData['criteriaEnabled']);
        }
        if (isset($parametersData['criteriaEnabled']) && $parametersData['criteriaEnabled'] && isset($parametersData['criteria'])) {
            $this->deserializeCriteria($dropzone, $parametersData['criteria']);
        }
        if (isset($parametersData['criteriaTotal'])) {
            $dropzone->setCriteriaTotal($parametersData['criteriaTotal']);
        }
        if (isset($parametersData['autoCloseDropsAtDropEndDate'])) {
            $dropzone->setAutoCloseDropsAtDropEndDate($parametersData['autoCloseDropsAtDropEndDate']);
        }
    }

    private function serializePlanning(Dropzone $dropzone)
    {
        if ($dropzone->getManualPlanning()) {
            return [
                'type' => 'manual',
                'state' => $dropzone->getManualState(),
            ];
        } else {
            return [
                'type' => 'auto',
                'drop' => [
                    DateNormalizer::normalize($dropzone->getDropStartDate()),
                    DateNormalizer::normalize($dropzone->getDropEndDate()),
                ],
                'review' => [
                    DateNormalizer::normalize($dropzone->getReviewStartDate()),
                    DateNormalizer::normalize($dropzone->getReviewEndDate()),
                ],
            ];
        }
    }

    private function deserializePlanning(array $planningData, Dropzone $dropzone)
    {
        if (isset($planningData['type'])) {
            $dropzone->setManualPlanning('manual' === $planningData['type']);
        }

        if ($dropzone->getManualPlanning()) {
            $dropzone->setManualState($planningData['state']);

            // reset auto dates
            $dropzone->setDropStartDate(null);
            $dropzone->setDropEndDate(null);

            $dropzone->setReviewStartDate(null);
            $dropzone->setReviewEndDate(null);
        } else {
            $dropzone->setDropStartDate(DateNormalizer::denormalize($planningData['drop'][0]));
            $dropzone->setDropEndDate(DateNormalizer::denormalize($planningData['drop'][1]));

            $dropzone->setReviewStartDate(DateNormalizer::denormalize($planningData['review'][0]));
            $dropzone->setReviewEndDate(DateNormalizer::denormalize($planningData['review'][1]));
        }
    }

    private function serializeDisplay(Dropzone $dropzone)
    {
        return [
            'correctionInstruction' => $dropzone->getCorrectionInstruction(),
            'successMessage' => $dropzone->getSuccessMessage(),
            'failMessage' => $dropzone->getFailMessage(),
            'showScore' => $dropzone->getDisplayNotationToLearners(),
            'showFeedback' => $dropzone->getDisplayNotationMessageToLearners(),
            'displayCorrectionsToLearners' => $dropzone->getDisplayCorrectionsToLearners(),
        ];
    }

    private function serializeNotifications(Dropzone $dropzone)
    {
        return [
            'enabled' => $dropzone->getNotifyOnDrop(),
            'actions' => ['drop'],
        ];
    }

    private function serializeCriteria(Dropzone $dropzone)
    {
        return array_map(function (Criterion $criterion) {
            return $this->criterionSerializer->serialize($criterion);
        }, $dropzone->getCriteria());
    }

    private function deserializeCriteria(Dropzone $dropzone, array $criteriaData)
    {
        foreach ($criteriaData as $criterionData) {
            $criterion = $this->criterionSerializer->deserialize('Claroline\DropZoneBundle\Entity\Criterion', $criterionData);
            $dropzone->addCriterion($criterion);
        }
    }
}
