<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\EvaluationBundle\Manager\ResourceEvaluationManager;
use Claroline\ScormBundle\Entity\Sco;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Entity\ScoTracking;
use Claroline\ScormBundle\Library\ScormLib;
use Claroline\ScormBundle\Manager\Exception\InvalidScormArchiveException;
use Claroline\ScormBundle\Serializer\ScormSerializer;
use Claroline\ScormBundle\Serializer\ScoSerializer;
use Claroline\ScormBundle\Serializer\ScoTrackingSerializer;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\File;

class ScormManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var string */
    private $filesDir;
    /** @var ObjectManager */
    private $om;
    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;
    /** @var ScormLib */
    private $scormLib;
    /** @var ScormSerializer */
    private $scormSerializer;
    /** @var ScoSerializer */
    private $scoSerializer;
    /** @var ScoTrackingSerializer */
    private $scoTrackingSerializer;
    /** @var string */
    private $uploadDir;

    private $resourceUserEvalRepo;
    private $scoTrackingRepo;

    /**
     * Constructor.
     *
     * @param string $filesDir
     * @param string $uploadDir
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        $filesDir,
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager,
        ScormLib $scormLib,
        ScormSerializer $scormSerializer,
        ScoSerializer $scoSerializer,
        ScoTrackingSerializer $scoTrackingSerializer,
        $uploadDir
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->filesDir = $filesDir;
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->scormLib = $scormLib;
        $this->scormSerializer = $scormSerializer;
        $this->scoSerializer = $scoSerializer;
        $this->scoTrackingSerializer = $scoTrackingSerializer;
        $this->uploadDir = $uploadDir;

        $this->resourceUserEvalRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceUserEvaluation');
        $this->scoTrackingRepo = $om->getRepository('ClarolineScormBundle:ScoTracking');
    }

    public function uploadScormArchive(Workspace $workspace, File $file)
    {
        // Checks if it is a valid scorm archive
        $zip = new \ZipArchive();
        $openValue = $zip->open($file);

        $isScormArchive = (true === $openValue) && $zip->getStream('imsmanifest.xml');

        $zip->close();

        if (!$isScormArchive) {
            throw new InvalidScormArchiveException('invalid_scorm_archive_message');
        } else {
            return $this->generateScorm($workspace, $file);
        }
    }

    public function updateScorm(Scorm $scorm, $data)
    {
        $newScorm = $this->scormSerializer->deserialize($data, $scorm);
        $this->om->persist($newScorm);
        $this->om->flush();

        return $this->scormSerializer->serialize($newScorm);
    }

    public function generateScoTracking(Sco $sco, User $user = null)
    {
        $tracking = null;

        if (!is_null($user)) {
            $tracking = $this->scoTrackingRepo->findOneBy(['sco' => $sco, 'user' => $user]);
        }
        if (is_null($tracking)) {
            $tracking = $this->createScoTracking($sco, $user);
        }

        return $tracking;
    }

    public function generateScosTrackings(array $scos, User $user = null, &$trackings = [])
    {
        if (!is_null($user)) {
            $this->om->startFlushSuite();
        }

        foreach ($scos as $sco) {
            $tracking = $this->generateScoTracking($sco, $user);
            $trackings[$sco->getUuid()] = $this->scoTrackingSerializer->serialize($tracking);
            $scoChildren = $sco->getScoChildren()->toArray();

            if (0 < count($scoChildren)) {
                $this->generateScosTrackings($scoChildren, $user, $trackings);
            }
        }
        if (!is_null($user)) {
            $this->om->endFlushSuite();
        }

        return $trackings;
    }

    public function parseScormArchive(File $file)
    {
        $data = [];
        $contents = '';
        $zip = new \ZipArchive();

        $zip->open($file);
        $stream = $zip->getStream('imsmanifest.xml');

        while (!feof($stream)) {
            $contents .= fread($stream, 2);
        }
        $dom = new \DOMDocument();

        if (!$dom->loadXML($contents)) {
            throw new InvalidScormArchiveException('cannot_load_imsmanifest_message');
        }

        $scormVersionElements = $dom->getElementsByTagName('schemaversion');

        if (1 === $scormVersionElements->length) {
            switch ($scormVersionElements->item(0)->textContent) {
                case '1.2':
                    $data['version'] = Scorm::SCORM_12;
                    break;
                case 'CAM 1.3':
                case '2004 3rd Edition':
                case '2004 4th Edition':
                    $data['version'] = Scorm::SCORM_2004;
                    break;
                default:
                    throw new InvalidScormArchiveException('invalid_scorm_version_message');
            }
        } else {
            throw new InvalidScormArchiveException('invalid_scorm_version_message');
        }
        $scos = $this->scormLib->parseOrganizationsNode($dom);

        if (0 >= count($scos)) {
            throw new InvalidScormArchiveException('no_sco_in_scorm_archive_message');
        }
        $data['scos'] = array_map(function (Sco $sco) {
            return $this->scoSerializer->serialize($sco);
        }, $scos);

        return $data;
    }

    public function createScoTracking(Sco $sco, User $user = null)
    {
        $version = $sco->getScorm()->getVersion();
        $scoTracking = new ScoTracking();
        $scoTracking->setSco($sco);

        switch ($version) {
            case Scorm::SCORM_12:
                $scoTracking->setLessonStatus('not attempted');
                $scoTracking->setSuspendData('');
                $scoTracking->setEntry('ab-initio');
                $scoTracking->setLessonLocation('');
                $scoTracking->setCredit('no-credit');
                $scoTracking->setTotalTimeInt(0);
                $scoTracking->setSessionTime(0);
                $scoTracking->setLessonMode('normal');
                $scoTracking->setExitMode('');

                if (is_null($sco->getPrerequisites())) {
                    $scoTracking->setIsLocked(false);
                } else {
                    $scoTracking->setIsLocked(true);
                }
                break;
            case Scorm::SCORM_2004:
                $scoTracking->setTotalTimeString('PT0S');
                $scoTracking->setCompletionStatus('unknown');
                $scoTracking->setLessonStatus('unknown');
                $scoTracking->setIsLocked(false);
                break;
        }
        if (!empty($user)) {
            $scoTracking->setUser($user);
            $this->om->persist($scoTracking);
            $this->om->flush();
        }

        return $scoTracking;
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
                if (empty($bestStatus) || ($lessonStatus !== $bestStatus && $statusPriority[$lessonStatus] > $statusPriority[$bestStatus])) {
                    $tracking->setLessonStatus($lessonStatus);
                    $bestStatus = $lessonStatus;
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

    public function generateScormEvaluation(ScoTracking $tracking, $sessionTime = null)
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
            $this->resourceEvalManager->updateResourceEvaluation($attempt, $evaluationData, $tracking->getLatestDate());
        } else {
            $attempt = $this->resourceEvalManager->createResourceEvaluation(
                $scorm->getResourceNode(),
                $tracking->getUser(),
                $evaluationData,
                $tracking->getLatestDate()
            );
        }

        return $attempt;
    }

    /**
     * Unzip a given ZIP file into the web resources directory.
     *
     * @param string $hashName name of the destination directory
     */
    public function unzipScormArchive(Workspace $workspace, File $file, $hashName)
    {
        $zip = new \ZipArchive();
        $zip->open($file);
        $ds = DIRECTORY_SEPARATOR;
        $destinationDir = $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName;

        if (!file_exists($destinationDir)) {
            mkdir($destinationDir, 0777, true);
        }

        $zip->extractTo($destinationDir);
        $zip->close();
    }

    private function generateScorm(Workspace $workspace, File $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hashName = Uuid::uuid4()->toString().'.zip';
        $scormData = $this->parseScormArchive($file);
        $this->unzipScormArchive($workspace, $file, $hashName);
        // Move Scorm archive in the files directory
        $finalFile = $file->move($this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid(), $hashName);

        return [
            'name' => $hashName, // to follow standard file data format
            'hashName' => $hashName,
            'type' => $finalFile->getMimeType(),
            'version' => $scormData['version'],
            'scos' => $scormData['scos'],
        ];
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
