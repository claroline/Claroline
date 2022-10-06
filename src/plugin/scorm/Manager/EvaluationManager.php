<?php

namespace Claroline\ScormBundle\Manager;

use Claroline\AppBundle\API\SerializerProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\ScormBundle\Entity\Sco;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Entity\ScoTracking;

class EvaluationManager
{
    /** @var ObjectManager */
    private $om;
    /** @var SerializerProvider */
    private $serializer;
    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;

    private $scoTrackingRepo;

    public function __construct(
        ObjectManager $om,
        SerializerProvider $serializer,
        ResourceEvaluationManager $resourceEvalManager
    ) {
        $this->om = $om;
        $this->serializer = $serializer;
        $this->resourceEvalManager = $resourceEvalManager;

        $this->scoTrackingRepo = $om->getRepository(ScoTracking::class);
    }

    public function getResourceUserEvaluation(Scorm $scorm, User $user): ResourceUserEvaluation
    {
        return $this->resourceEvalManager->getUserEvaluation($scorm->getResourceNode(), $user);
    }

    public function generateScosTrackings(array $scos, User $user): array
    {
        $this->om->startFlushSuite();

        $tracking = [];
        foreach ($scos as $sco) {
            $tracking[$sco->getUuid()] = $this->serializer->serialize(
                $this->generateScoTracking($sco, $user)
            );

            $scoChildren = $sco->getScoChildren()->toArray();
            if (0 < count($scoChildren)) {
                $tracking = array_merge($tracking, $this->generateScosTrackings($scoChildren, $user));
            }
        }

        $this->om->endFlushSuite();

        return $tracking;
    }

    public function updateScoTracking(Sco $sco, User $user, $data)
    {
        $tracking = $this->generateScoTracking($sco, $user);

        $statusPriority = [
            'unknown' => 0,
            'not attempted' => 1,
            'not_attempted' => 1,
            'browsed' => 2,
            'incomplete' => 3,
            'completed' => 4,
            'failed' => 5,
            'passed' => 6,
        ];

        $duration = null;
        switch ($sco->getScorm()->getVersion()) {
            case Scorm::SCORM_12:
                $scoreRaw = isset($data['cmi.core.score.raw']) ? intval($data['cmi.core.score.raw']) : null;
                $scoreMin = isset($data['cmi.core.score.min']) ? intval($data['cmi.core.score.min']) : null;
                $scoreMax = isset($data['cmi.core.score.max']) ? intval($data['cmi.core.score.max']) : null;
                $lessonStatus = isset($data['cmi.core.lesson_status']) ? $data['cmi.core.lesson_status'] : null;
                $sessionTime = isset($data['cmi.core.session_time']) ? $data['cmi.core.session_time'] : null;
                $sessionTimeInHundredth = $this->convertTimeInHundredth($sessionTime);
                $duration = $sessionTimeInHundredth / 100;
                $progression = isset($data['cmi.progress_measure']) ? floatval($data['cmi.progress_measure']) : 0;

                $tracking->setEntry($data['cmi.core.entry']);
                $tracking->setExitMode($data['cmi.core.exit']);
                $tracking->setLessonLocation($data['cmi.core.lesson_location']);
                $tracking->setSessionTime($sessionTimeInHundredth);

                // Compute total time
                $totalTimeInHundredth = $this->convertTimeInHundredth($data['cmi.core.total_time']);
                $totalTimeInHundredth += $sessionTimeInHundredth;
                // Persist total time
                $tracking->setTotalTime($totalTimeInHundredth);

                // Update best score if the current score is better than the previous best score
                $bestScore = $tracking->getScoreRaw();
                if (empty($bestScore) || (!is_null($scoreRaw) && $scoreRaw > $bestScore)) {
                    $tracking->setScoreRaw($scoreRaw);
                    $tracking->setScoreMin($scoreMin);
                    $tracking->setScoreMax($scoreMax);
                }

                $bestStatus = $tracking->getLessonStatus();
                if (!empty($lessonStatus)) {
                    if (empty($bestStatus) || ($lessonStatus !== $bestStatus && $statusPriority[$lessonStatus] > $statusPriority[$bestStatus])) {
                        $tracking->setLessonStatus($lessonStatus);
                        $bestStatus = $lessonStatus;
                    }
                }

                if (empty($progression) && ('completed' === $bestStatus || 'passed' === $bestStatus)) {
                    $progression = 100;
                }

                if ($progression > $tracking->getProgression()) {
                    $tracking->setProgression($progression);
                }

                break;

            case Scorm::SCORM_2004:
                $duration = isset($data['cmi.session_time']) ?
                    $this->formatSessionTime($data['cmi.session_time']) :
                    'PT0S';
                $completionStatus = isset($data['cmi.completion_status']) ? $data['cmi.completion_status'] : 'unknown';
                $successStatus = isset($data['cmi.success_status']) ? $data['cmi.success_status'] : 'unknown';
                $scoreRaw = isset($data['cmi.score.raw']) ? intval($data['cmi.score.raw']) : null;
                $scoreMin = isset($data['cmi.score.min']) ? intval($data['cmi.score.min']) : null;
                $scoreMax = isset($data['cmi.score.max']) ? intval($data['cmi.score.max']) : null;
                $scoreScaled = isset($data['cmi.score.scaled']) ? floatval($data['cmi.score.scaled']) : null;
                $progression = isset($data['cmi.progress_measure']) ? floatval($data['cmi.progress_measure']) : 0;

                // Computes total time
                $totalTime = new \DateInterval($tracking->getTotalTimeString());

                try {
                    $sessionTime = new \DateInterval($duration);
                } catch (\Exception $e) {
                    $sessionTime = new \DateInterval('PT0S');
                }
                $computedTime = new \DateTime();
                $computedTime->setTimestamp(0);
                $computedTime->add($totalTime);
                $computedTime->add($sessionTime);
                $computedTimeInSecond = $computedTime->getTimestamp();
                $totalTimeInterval = $this->retrieveIntervalFromSeconds($computedTimeInSecond);
                $data['cmi.total_time'] = $totalTimeInterval;
                $tracking->setTotalTimeString($totalTimeInterval);

                // Update best score if the current score is better than the previous best score
                $bestScore = $tracking->getScoreRaw();
                if (empty($bestScore) || (!is_null($scoreRaw) && $scoreRaw > $bestScore)) {
                    $tracking->setScoreRaw($scoreRaw);
                    $tracking->setScoreMin($scoreMin);
                    $tracking->setScoreMax($scoreMax);
                    $tracking->setScoreScaled($scoreScaled);
                }

                // Update best success status and completion status
                // merge both status in one prop to match the Claroline model
                $lessonStatus = $completionStatus;
                if (in_array($successStatus, ['passed', 'failed'])) {
                    $lessonStatus = $successStatus;
                }

                $bestStatus = $tracking->getLessonStatus();
                if (empty($bestStatus) || ($lessonStatus !== $bestStatus && $statusPriority[$lessonStatus] > $statusPriority[$bestStatus])) {
                    $tracking->setLessonStatus($lessonStatus);
                    $bestStatus = $lessonStatus;
                }

                if (empty($tracking->getCompletionStatus())
                    || ($completionStatus !== $tracking->getCompletionStatus() && $statusPriority[$completionStatus] > $statusPriority[$tracking->getCompletionStatus()])
                ) {
                    // This is no longer needed as completionStatus and successStatus are merged together
                    // I keep it for now for possible retro compatibility
                    $tracking->setCompletionStatus($completionStatus);
                }

                if (empty($progression) && ('completed' === $bestStatus || 'passed' === $bestStatus)) {
                    $progression = 100;
                }

                if ($progression > $tracking->getProgression()) {
                    $tracking->setProgression($progression);
                }

                break;
        }

        $tracking->setLatestDate(new \DateTime());
        if (isset($data['cmi.suspend_data'])) {
            $tracking->setSuspendData($data['cmi.suspend_data']);
        }

        $attempt = $this->generateScormEvaluation($tracking, $duration);

        $tracking->setDetails(array_merge($data, [
            'sco' => $sco->getUuid(),
            'attempt' => $attempt->getId(),
        ]));

        $this->om->persist($tracking);
        $this->om->flush();

        return $tracking;
    }

    private function generateScormEvaluation(ScoTracking $tracking, $sessionTime = null)
    {
        $scorm = $tracking->getSco()->getScorm();

        $status = 'unknown';
        switch ($tracking->getLessonStatus()) {
            case 'passed':
            case 'failed':
            case 'completed':
            case 'incomplete':
                $status = $tracking->getLessonStatus();
                break;
            case 'not attempted':
                $status = 'not_attempted';
                break;
            case 'browsed':
                $status = 'opened';
                break;
        }

        $duration = null;
        switch ($scorm->getVersion()) {
            case Scorm::SCORM_12:
                $duration = $sessionTime;
                break;
            case Scorm::SCORM_2004:
                if (!is_null($sessionTime)) {
                    $time = new \DateInterval($sessionTime);
                    $computedTime = new \DateTime();
                    $computedTime->setTimestamp(0);
                    $computedTime->add($time);
                    $duration = $computedTime->getTimestamp();
                }
        }

        $evaluationData = [
            'progression' => $tracking->getProgression(),
            'status' => $status,
            'score' => $tracking->getScoreRaw(),
            'scoreMin' => $tracking->getScoreMin(),
            'scoreMax' => $tracking->getScoreMax(),
            'duration' => $duration, // todo : retrieve from ScoTracking
            'data' => $tracking->getDetails(),
        ];

        $attempt = null;
        $details = $tracking->getDetails();
        if (!empty($details) && !empty($details['attempt'])) {
            $attempt = $this->om->getRepository(ResourceEvaluation::class)->find($details['attempt']);
        }

        if (!empty($attempt)) {
            return $this->resourceEvalManager->updateResourceEvaluation($attempt, $evaluationData, $tracking->getLatestDate());
        }

        return $this->resourceEvalManager->createResourceEvaluation(
            $scorm->getResourceNode(),
            $tracking->getUser(),
            $evaluationData,
            $tracking->getLatestDate()
        );
    }

    private function generateScoTracking(Sco $sco, User $user): ScoTracking
    {
        $tracking = $this->scoTrackingRepo->findOneBy(['sco' => $sco, 'user' => $user]);
        if (empty($tracking)) {
            $tracking = new ScoTracking();
            $tracking->setSco($sco);
            $tracking->setUser($user);

            switch ($sco->getScorm()->getVersion()) {
                case Scorm::SCORM_12:
                    $tracking->setLessonStatus('not attempted');
                    $tracking->setSuspendData('');
                    $tracking->setEntry('ab-initio');
                    $tracking->setLessonLocation('');
                    $tracking->setCredit('no-credit');
                    $tracking->setTotalTimeInt(0);
                    $tracking->setSessionTime(0);
                    $tracking->setLessonMode('normal');
                    $tracking->setExitMode('');

                    if (is_null($sco->getPrerequisites())) {
                        $tracking->setIsLocked(false);
                    } else {
                        $tracking->setIsLocked(true);
                    }
                    break;
                case Scorm::SCORM_2004:
                    $tracking->setTotalTimeString('PT0S');
                    $tracking->setCompletionStatus('unknown');
                    $tracking->setLessonStatus('unknown');
                    $tracking->setIsLocked(false);
                    break;
            }

            $this->om->persist($tracking);
            $this->om->flush();
        }

        return $tracking;
    }

    private function convertTimeInHundredth($time)
    {
        $timeInArray = explode(':', $time);
        $timeInArraySec = explode('.', $timeInArray[2]);
        $timeInHundredth = 0;

        if (isset($timeInArraySec[1])) {
            if (1 === strlen($timeInArraySec[1])) {
                $timeInArraySec[1] .= '0';
            }
            $timeInHundredth = intval($timeInArraySec[1]);
        }
        $timeInHundredth += intval($timeInArraySec[0]) * 100;
        $timeInHundredth += intval($timeInArray[1]) * 6000;
        $timeInHundredth += intval($timeInArray[0]) * 360000;

        return $timeInHundredth;
    }

    /**
     * Converts a time in seconds to a DateInterval string.
     *
     * @param int $seconds
     *
     * @return string
     */
    private function retrieveIntervalFromSeconds($seconds)
    {
        $result = '';
        $remainingTime = (int) $seconds;

        if (empty($remainingTime)) {
            $result .= 'PT0S';
        } else {
            $nbDays = (int) ($remainingTime / 86400);
            $remainingTime %= 86400;
            $nbHours = (int) ($remainingTime / 3600);
            $remainingTime %= 3600;
            $nbMinutes = (int) ($remainingTime / 60);
            $nbSeconds = $remainingTime % 60;
            $result .= 'P'.$nbDays.'DT'.$nbHours.'H'.$nbMinutes.'M'.$nbSeconds.'S';
        }

        return $result;
    }

    private function formatSessionTime($sessionTime)
    {
        $formattedValue = 'PT0S';
        $generalPattern = '/^P([0-9]+Y)?([0-9]+M)?([0-9]+D)?T([0-9]+H)?([0-9]+M)?([0-9]+S)?$/';
        $decimalPattern = '/^P([0-9]+Y)?([0-9]+M)?([0-9]+D)?T([0-9]+H)?([0-9]+M)?[0-9]+\.[0-9]{1,2}S$/';

        if ('PT' !== $sessionTime) {
            if (preg_match($generalPattern, $sessionTime)) {
                $formattedValue = $sessionTime;
            } elseif (preg_match($decimalPattern, $sessionTime)) {
                $formattedValue = preg_replace(['/\.[0-9]+S$/'], ['S'], $sessionTime);
            }
        }

        return $formattedValue;
    }
}
