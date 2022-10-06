<?php

namespace Claroline\DropZoneBundle\Serializer;

use Claroline\AppBundle\API\Serializer\SerializerTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Library\Normalizer\DateRangeNormalizer;
use Claroline\DropZoneBundle\Entity\Criterion;
use Claroline\DropZoneBundle\Entity\Dropzone;

class DropzoneSerializer
{
    use SerializerTrait;

    /** @var CriterionSerializer */
    private $criterionSerializer;
    /** @var ObjectManager */
    private $om;

    public function __construct(
        CriterionSerializer $criterionSerializer,
        ObjectManager $om
    ) {
        $this->criterionSerializer = $criterionSerializer;
        $this->om = $om;
    }

    public function getName()
    {
        return 'dropzone';
    }

    public function serialize(Dropzone $dropzone): array
    {
        return [
            'id' => $dropzone->getUuid(),
            'instruction' => $dropzone->getInstruction(),
            'parameters' => $this->serializeParameters($dropzone),
            'display' => $this->serializeDisplay($dropzone),
            'planning' => $this->serializePlanning($dropzone),
            'notifications' => $this->serializeNotifications($dropzone),
            'restrictions' => [
                'lockDrops' => $dropzone->hasLockDrops(),
            ],
        ];
    }

    public function deserialize(array $data, Dropzone $dropzone): Dropzone
    {
        $this->sipe('instruction', 'setInstruction', $data, $dropzone);

        if (isset($data['parameters'])) {
            $this->deserializeParameters($data, $dropzone);
        }

        $this->sipe('display.correctionInstruction', 'setCorrectionInstruction', $data, $dropzone);
        $this->sipe('display.successMessage', 'setSuccessMessage', $data, $dropzone);
        $this->sipe('display.failMessage', 'setFailMessage', $data, $dropzone);
        $this->sipe('display.showScore', 'setDisplayNotationToLearners', $data, $dropzone);
        $this->sipe('display.showFeedback', 'setDisplayNotationMessageToLearners', $data, $dropzone);
        $this->sipe('display.displayCorrectionsToLearners', 'setDisplayCorrectionsToLearners', $data, $dropzone);
        $this->sipe('display.correctorDisplayed', 'setCorrectorDisplayed', $data, $dropzone);
        $this->sipe('restrictions.lockDrops', 'setLockDrops', $data, $dropzone);

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
            'revisionEnabled' => $dropzone->isRevisionEnabled(),
        ];
    }

    private function deserializeParameters(array $data, Dropzone $dropzone)
    {
        if (isset($data['parameters']['reviewType'])) {
            $dropzone->setPeerReview('peer' === $data['parameters']['reviewType']);
        }
        $this->sipe('parameters.dropType', 'setDropType', $data, $dropzone);
        $this->sipe('parameters.documents', 'setAllowedDocuments', $data, $dropzone);
        $this->sipe('parameters.expectedCorrectionTotal', 'setExpectedCorrectionTotal', $data, $dropzone);
        $this->sipe('parameters.scoreToPass', 'setScoreToPass', $data, $dropzone);
        $this->sipe('parameters.commentInCorrectionEnabled', 'setCommentInCorrectionEnabled', $data, $dropzone);
        $this->sipe('parameters.commentInCorrectionForced', 'setCommentInCorrectionForced', $data, $dropzone);
        $this->sipe('parameters.correctionDenialEnabled', 'setCorrectionDenialEnabled', $data, $dropzone);
        $this->sipe('parameters.criteriaEnabled', 'setCriteriaEnabled', $data, $dropzone);
        $this->sipe('parameters.criteriaTotal', 'setCriteriaTotal', $data, $dropzone);
        $this->sipe('parameters.autoCloseDropsAtDropEndDate', 'setAutoCloseDropsAtDropEndDate', $data, $dropzone);
        $this->sipe('parameters.revisionEnabled', 'setRevisionEnabled', $data, $dropzone);

        if (!empty($data['parameters']['scoreMax']) && $data['parameters']['scoreMax'] !== $dropzone->getScoreMax()) {
            $dropzone->setScoreMax($data['parameters']['scoreMax']);
        }

        if (isset($data['parameters']['criteriaEnabled']) && $data['parameters']['criteriaEnabled'] && isset($data['parameters']['criteria'])) {
            $this->deserializeCriteria($dropzone, $data['parameters']['criteria']);
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
                'drop' => DateRangeNormalizer::normalize($dropzone->getDropStartDate(), $dropzone->getDropEndDate()),
                'review' => DateRangeNormalizer::normalize($dropzone->getReviewStartDate(), $dropzone->getReviewEndDate()),
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
            if (isset($planningData['drop'])) {
                $dropRange = DateRangeNormalizer::denormalize($planningData['drop']);
                $dropzone->setDropStartDate($dropRange[0]);
                $dropzone->setDropEndDate($dropRange[1]);
            }
            if (isset($planningData['review'])) {
                $reviewRange = DateRangeNormalizer::denormalize($planningData['review']);
                $dropzone->setReviewStartDate($reviewRange[0]);
                $dropzone->setReviewEndDate($reviewRange[1]);
            }
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
            'correctorDisplayed' => $dropzone->isCorrectorDisplayed(),
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
        $oldCriteria = $dropzone->getCriteria();
        $newCriteriaUuids = [];
        $dropzone->emptyCriteria();

        foreach ($criteriaData as $criterionData) {
            $criterion = $this->criterionSerializer->deserialize('Claroline\DropZoneBundle\Entity\Criterion', $criterionData);
            $dropzone->addCriterion($criterion);
            $newCriteriaUuids[] = $criterion->getUuid();
        }
        /* Removes previous fields that are not used anymore */
        foreach ($oldCriteria as $criterion) {
            if (!in_array($criterion->getUuid(), $newCriteriaUuids)) {
                $this->om->remove($criterion);
            }
        }
    }
}
