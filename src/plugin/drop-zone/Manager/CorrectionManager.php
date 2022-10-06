<?php

namespace Claroline\DropZoneBundle\Manager;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\User;
use Claroline\DropZoneBundle\Entity\Correction;
use Claroline\DropZoneBundle\Entity\Dropzone;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionDeleteEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionEndEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionReportEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionStartEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionUpdateEvent;
use Claroline\DropZoneBundle\Event\Log\LogCorrectionValidationChangeEvent;
use Claroline\DropZoneBundle\Repository\CorrectionRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CorrectionManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var DropManager */
    private $dropManager;
    /** @var EvaluationManager */
    private $evaluationManager;

    /** @var CorrectionRepository */
    private $correctionRepo;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om,
        SerializerProvider $serializer,
        DropManager $dropManager,
        EvaluationManager $evaluationManager
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->serializer = $serializer;
        $this->dropManager = $dropManager;
        $this->evaluationManager = $evaluationManager;

        $this->correctionRepo = $om->getRepository(Correction::class);
    }

    /**
     * Retrieves all corrections made for a Dropzone.
     *
     * @deprecated use crud instead
     */
    public function getAllCorrectionsData(Dropzone $dropzone): array
    {
        $data = [];

        $corrections = $this->correctionRepo->findAllCorrectionsByDropzone($dropzone);
        foreach ($corrections as $correction) {
            $teamId = $correction->getTeamUuid();
            $key = empty($teamId) ? 'user_'.$correction->getUser()->getUuid() : 'team_'.$teamId;

            if (!isset($data[$key])) {
                $data[$key] = [];
            }

            $data[$key][] = $this->serializer->serialize($correction);
        }

        return $data;
    }

    /**
     * @deprecated use crud instead
     */
    public function saveCorrection(array $data, User $user): Correction
    {
        $this->om->startFlushSuite();

        $existingCorrection = $this->correctionRepo->findOneBy(['uuid' => $data['id']]);
        $isNew = empty($existingCorrection);
        $correction = $this->serializer->get(Correction::class)->deserialize($data);
        $correction->setUser($user);
        $dropzone = $correction->getDrop()->getDropzone();

        if (!$isNew) {
            $correction->setLastEditionDate(new \DateTime());
        }
        $correction = $this->computeCorrectionScore($correction);
        $this->om->persist($correction);

        $this->om->endFlushSuite();

        if ($isNew) {
            $this->eventDispatcher->dispatch(new LogCorrectionStartEvent($dropzone, $correction->getDrop(), $correction), 'log');
        } else {
            $this->eventDispatcher->dispatch(new LogCorrectionUpdateEvent($dropzone, $correction->getDrop(), $correction), 'log');
        }

        return $correction;
    }

    public function submitCorrection(Correction $correction, User $user): Correction
    {
        $this->om->startFlushSuite();

        $correction->setFinished(true);
        $correction->setEndDate(new \DateTime());
        $correction->setUser($user);
        $this->om->persist($correction);
        $this->om->forceFlush();
        $drop = $this->evaluationManager->computeDropScore($correction->getDrop());
        $dropzone = $drop->getDropzone();
        $userDrop = null;
        $users = [];

        switch ($dropzone->getDropType()) {
            case Dropzone::DROP_TYPE_USER:
                $users = [$user];
                $userDrop = $this->evaluationManager->getUserDrop($dropzone, $user);
                break;
            case Dropzone::DROP_TYPE_TEAM:
                $teamDrops = $this->dropManager->getTeamDrops($dropzone, $user);

                if (1 === count($teamDrops)) {
                    $users = $teamDrops[0]->getUsers();
                    $userDrop = $teamDrops[0];
                }
                break;
        }
        $this->eventDispatcher->dispatch(new LogCorrectionEndEvent($dropzone, $correction->getDrop(), $correction), 'log');
        $this->om->forceFlush();

        $this->evaluationManager->checkSuccess($drop);
        $this->evaluationManager->checkCompletion($dropzone, $users, $userDrop);

        $this->om->endFlushSuite();

        return $correction;
    }

    public function switchCorrectionValidation(Correction $correction): Correction
    {
        $this->om->startFlushSuite();

        $correction->setValid(!$correction->isValid());
        $this->om->persist($correction);
        $drop = $this->evaluationManager->computeDropScore($correction->getDrop());
        $this->evaluationManager->checkSuccess($drop);

        $this->om->endFlushSuite();

        $this->eventDispatcher->dispatch(new LogCorrectionValidationChangeEvent($correction->getDrop()->getDropzone(), $correction->getDrop(), $correction), 'log');

        return $correction;
    }

    public function denyCorrection(Correction $correction, ?string $comment = null): Correction
    {
        $correction->setCorrectionDenied(true);
        $correction->setCorrectionDeniedComment($comment);
        $this->om->persist($correction);
        $this->om->flush();

        $this->eventDispatcher->dispatch(new LogCorrectionReportEvent($correction->getDrop()->getDropzone(), $correction->getDrop(), $correction), 'log');

        return $correction;
    }

    /**
     * @deprecated use crud instead
     */
    public function deleteCorrection(Correction $correction): void
    {
        $this->om->startFlushSuite();

        $drop = $correction->getDrop();
        $drop->removeCorrection($correction);
        $this->om->remove($correction);
        $drop = $this->evaluationManager->computeDropScore($drop);
        $this->evaluationManager->checkSuccess($drop);

        $this->om->endFlushSuite();

        $this->eventDispatcher->dispatch(new LogCorrectionDeleteEvent($correction->getDrop()->getDropzone(), $drop, $correction), 'log');
    }

    /**
     * Computes Correction score from criteria grades.
     */
    private function computeCorrectionScore(Correction $correction): Correction
    {
        $drop = $correction->getDrop();
        $dropzone = $drop->getDropzone();
        $criteria = $dropzone->getCriteria();

        if ($dropzone->isCriteriaEnabled() && count($criteria) > 0) {
            $score = 0;
            $criteriaIds = [];
            $scoreMax = $dropzone->getScoreMax();
            $total = ($dropzone->getCriteriaTotal() - 1) * count($criteria);
            $grades = $correction->getGrades();

            foreach ($criteria as $criterion) {
                $criteriaIds[] = $criterion->getUuid();
            }

            foreach ($grades as $grade) {
                $gradeCriterion = $grade->getCriterion();

                if (in_array($gradeCriterion->getUuid(), $criteriaIds)) {
                    $score += $grade->getValue();
                }
            }

            $score = round(($score / $total) * $scoreMax, 2);
            $correction->setScore($score);
        }

        $this->om->persist($correction);
        $this->om->flush();

        return $correction;
    }
}
