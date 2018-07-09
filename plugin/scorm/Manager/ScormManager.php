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
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Resource\ResourceRights;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Manager\Resource\ResourceEvaluationManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\ScormBundle\Entity\Sco;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Entity\ScoTracking;
use Claroline\ScormBundle\Event\Log\LogScormResultEvent;
use Claroline\ScormBundle\Library\ScormLib;
use Claroline\ScormBundle\Manager\Exception\InvalidScormArchiveException;
use Claroline\ScormBundle\Serializer\ScormSerializer;
use Claroline\ScormBundle\Serializer\ScoSerializer;
use Claroline\ScormBundle\Serializer\ScoTrackingSerializer;
use JMS\DiExtraBundle\Annotation as DI;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @DI\Service("claroline.manager.scorm_manager")
 */
class ScormManager
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    /** @var Filesystem */
    private $fileSystem;
    /** @var string */
    private $filesDir;
    /** @var ObjectManager */
    private $om;
    /** @var ResourceEvaluationManager */
    private $resourceEvalManager;
    /** @var ResourceManager */
    private $resourceManager;
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
    private $scorm12ResourceRepo;
    private $scorm12ScoTrackingRepo;
    private $scorm2004ResourceRepo;
    private $scorm2004ScoTrackingRepo;
    private $shortcutRepo;
    private $logRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "eventDispatcher"       = @DI\Inject("event_dispatcher"),
     *     "fileSystem"            = @DI\Inject("filesystem"),
     *     "filesDir"              = @DI\Inject("%claroline.param.files_directory%"),
     *     "om"                    = @DI\Inject("claroline.persistence.object_manager"),
     *     "resourceEvalManager"   = @DI\Inject("claroline.manager.resource_evaluation_manager"),
     *     "resourceManager"       = @DI\Inject("claroline.manager.resource_manager"),
     *     "scormLib"              = @DI\Inject("claroline.library.scorm"),
     *     "scormSerializer"       = @DI\Inject("claroline.serializer.scorm"),
     *     "scoSerializer"         = @DI\Inject("claroline.serializer.scorm.sco"),
     *     "scoTrackingSerializer" = @DI\Inject("claroline.serializer.scorm.sco.tracking"),
     *     "uploadDir"             = @DI\Inject("%claroline.param.uploads_directory%")
     * })
     *
     * @param EventDispatcherInterface  $eventDispatcher
     * @param Filesystem                $fileSystem
     * @param string                    $filesDir
     * @param ObjectManager             $om
     * @param ResourceEvaluationManager $resourceEvalManager
     * @param ResourceManager           $resourceManager
     * @param ScormLib                  $scormLib
     * @param ScormSerializer           $scormSerializer
     * @param ScoSerializer             $scoSerializer
     * @param ScoTrackingSerializer     $scoTrackingSerializer
     * @param string                    $uploadDir
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        Filesystem $fileSystem,
        $filesDir,
        ObjectManager $om,
        ResourceEvaluationManager $resourceEvalManager,
        ResourceManager $resourceManager,
        ScormLib $scormLib,
        ScormSerializer $scormSerializer,
        ScoSerializer $scoSerializer,
        ScoTrackingSerializer $scoTrackingSerializer,
        $uploadDir
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->fileSystem = $fileSystem;
        $this->filesDir = $filesDir;
        $this->om = $om;
        $this->resourceEvalManager = $resourceEvalManager;
        $this->resourceManager = $resourceManager;
        $this->scormLib = $scormLib;
        $this->scormSerializer = $scormSerializer;
        $this->scoSerializer = $scoSerializer;
        $this->scoTrackingSerializer = $scoTrackingSerializer;
        $this->uploadDir = $uploadDir;

        $this->resourceUserEvalRepo = $om->getRepository('ClarolineCoreBundle:Resource\ResourceUserEvaluation');
        $this->scoTrackingRepo = $om->getRepository('ClarolineScormBundle:ScoTracking');
        $this->scorm12ResourceRepo = $om->getRepository('ClarolineScormBundle:Scorm12Resource');
        $this->scorm12ScoTrackingRepo = $om->getRepository('ClarolineScormBundle:Scorm12ScoTracking');
        $this->scorm2004ResourceRepo = $om->getRepository('ClarolineScormBundle:Scorm2004Resource');
        $this->scorm2004ScoTrackingRepo = $om->getRepository('ClarolineScormBundle:Scorm2004ScoTracking');
        $this->shortcutRepo = $om->getRepository('ClarolineLinkBundle:Resource\Shortcut');
        $this->logRepo = $om->getRepository('ClarolineCoreBundle:Log\Log');
    }

    public function uploadScormArchive(Workspace $workspace, UploadedFile $file)
    {
        // Checks if it is a valid scorm archive
        $zip = new \ZipArchive();
        $openValue = $zip->open($file);

        $isScormArchive = (true === $openValue) && $zip->getStream('imsmanifest.xml');

        if (!$isScormArchive) {
            throw new InvalidScormArchiveException('invalid_scorm_archive_message');
        } else {
            return $this->generateScorm($workspace, $file);
        }
    }

    public function generateScorm(Workspace $workspace, UploadedFile $file)
    {
        $ds = DIRECTORY_SEPARATOR;
        $hashName = Uuid::uuid4()->toString().'.zip';
        $scormData = $this->parseScormArchive($file);
        $this->unzipScormArchive($workspace, $file, $hashName);
        // Move Scorm archive in the files directory
        $file->move($this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid(), $hashName);

        return [
            'hashName' => $hashName,
            'version' => $scormData['version'],
            'scos' => $scormData['scos'],
        ];
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

    public function parseScormArchive(UploadedFile $file)
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

    public function updateScoTracking(Sco $sco, User $user, $mode, $data)
    {
        $tracking = $this->generateScoTracking($sco, $user);
        $tracking->setLatestDate(new \DateTime());

        switch ($sco->getScorm()->getVersion()) {
            case Scorm::SCORM_12:
                $scoreRaw = isset($data['cmi.core.score.raw']) ? intval($data['cmi.core.score.raw']) : null;
                $scoreMin = isset($data['cmi.core.score.min']) ? intval($data['cmi.core.score.min']) : null;
                $scoreMax = isset($data['cmi.core.score.max']) ? intval($data['cmi.core.score.max']) : null;
                $lessonStatus = isset($data['cmi.core.lesson_status']) ? $data['cmi.core.lesson_status'] : null;
                $sessionTime = isset($data['cmi.core.session_time']) ? $data['cmi.core.session_time'] : null;
                $sessionTimeInHundredth = $this->convertTimeInHundredth($sessionTime);
                $tracking->setDetails($data);
                $tracking->setEntry($data['cmi.core.entry']);
                $tracking->setExitMode($data['cmi.core.exit']);
                $tracking->setLessonLocation($data['cmi.core.lesson_location']);
                $tracking->setSessionTime($sessionTimeInHundredth);
                $tracking->setSuspendData($data['cmi.suspend_data']);

                if ('log' === $mode) {
                    // Compute total time
                    $totalTimeInHundredth = $this->convertTimeInHundredth($data['cmi.core.total_time']);
                    $totalTimeInHundredth += $sessionTimeInHundredth;
                    // Persist total time
                    $tracking->setTotalTime($totalTimeInHundredth);

                    $bestScore = $tracking->getScoreRaw();
                    $bestStatus = $tracking->getLessonStatus();

                    // Update best score if the current score is better than the previous best score
                    if (empty($bestScore) || (!is_null($scoreRaw) && $scoreRaw > $bestScore)) {
                        $tracking->setScoreRaw($scoreRaw);
                        $bestScore = $scoreRaw;
                    }
                    // Update best lesson status if :
                    // - current best status = 'not attempted'
                    // - current best status = 'browsed' or 'incomplete'
                    //   and current status = 'failed' or 'passed' or 'completed'
                    // - current best status = 'failed'
                    //   and current status = 'passed' or 'completed'
                    if ($lessonStatus !== $bestStatus && 'passed' !== $bestStatus && 'completed' !== $bestStatus) {
                        if (('not attempted' === $bestStatus && !empty($lessonStatus)) ||
                            (('browsed' === $bestStatus || 'incomplete' === $bestStatus)
                                && ('failed' === $lessonStatus || 'passed' === $lessonStatus || 'completed' === $lessonStatus)) ||
                            ('failed' === $bestStatus && ('passed' === $lessonStatus || 'completed' === $lessonStatus))
                        ) {
                            $tracking->setLessonStatus($lessonStatus);
                            $bestStatus = $lessonStatus;
                        }
                    }
                    $data['sco'] = $sco->getUuid();
                    $data['lessonStatus'] = $lessonStatus;
                    $data['scoreMax'] = $scoreMax;
                    $data['scoreMin'] = $scoreMin;
                    $data['scoreRaw'] = $scoreRaw;
                    $data['sessionTime'] = $sessionTimeInHundredth;
                    $data['totalTime'] = $totalTimeInHundredth;
                    $data['bestScore'] = $bestScore;
                    $data['bestStatus'] = $bestStatus;
                    $event = new LogScormResultEvent($sco->getScorm(), $user, $data);
                    $this->eventDispatcher->dispatch('log', $event);

                    // Generate resource evaluation
                    $this->generateScormEvaluation(
                        $tracking,
                        $data,
                        $scoreRaw,
                        $scoreMin,
                        $scoreMax,
                        $sessionTimeInHundredth / 100,
                        $lessonStatus
                    );
                }
                break;
            case Scorm::SCORM_2004:
                $tracking->setDetails($data);

                if (isset($data['cmi.suspend_data'])) {
                    $tracking->setSuspendData($data['cmi.suspend_data']);
                }

                if ('log' === $mode) {
                    $dataSessionTime = isset($data['cmi.session_time']) ?
                        $this->formatSessionTime($data['cmi.session_time']) :
                        'PT0S';
                    $completionStatus = isset($data['cmi.completion_status']) ? $data['cmi.completion_status'] : 'unknown';
                    $successStatus = isset($data['cmi.success_status']) ? $data['cmi.success_status'] : 'unknown';
                    $scoreRaw = isset($data['cmi.score.raw']) ? intval($data['cmi.score.raw']) : null;
                    $scoreMin = isset($data['cmi.score.min']) ? intval($data['cmi.score.min']) : null;
                    $scoreMax = isset($data['cmi.score.max']) ? intval($data['cmi.score.max']) : null;
                    $scoreScaled = isset($data['cmi.score.scaled']) ? floatval($data['cmi.score.scaled']) : null;
                    $bestScore = $tracking->getScoreRaw();

                    // Computes total time
                    $totalTime = new \DateInterval($tracking->getTotalTimeString());

                    try {
                        $sessionTime = new \DateInterval($dataSessionTime);
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
                    if (empty($bestScore) || (!is_null($scoreRaw) && $scoreRaw > $bestScore)) {
                        $tracking->setScoreRaw($scoreRaw);
                        $tracking->setScoreMin($scoreMin);
                        $tracking->setScoreMax($scoreMax);
                        $tracking->setScoreScaled($scoreScaled);
                    }

                    // Update best success status and completion status
                    $currentCompletionStatus = $tracking->getCompletionStatus();
                    $currentSuccessStatus = $tracking->getLessonStatus();
                    $conditionCA = ('unknown' === $currentCompletionStatus) &&
                        ('completed' === $completionStatus ||
                        'incomplete' === $completionStatus ||
                        'not_attempted' === $completionStatus);
                    $conditionCB = ('not_attempted' === $currentCompletionStatus) && ('completed' === $completionStatus || 'incomplete' === $completionStatus);
                    $conditionCC = ('incomplete' === $currentCompletionStatus) && ('completed' === $completionStatus);
                    $conditionSA = ('unknown' === $currentSuccessStatus) && ('passed' === $successStatus || 'failed' === $successStatus);
                    $conditionSB = ('failed' === $currentSuccessStatus) && ('passed' === $successStatus);

                    if (is_null($currentCompletionStatus) || $conditionCA || $conditionCB || $conditionCC) {
                        $tracking->setCompletionStatus($completionStatus);
                    }

                    if (is_null($currentSuccessStatus) || $conditionSA || $conditionSB) {
                        $tracking->setLessonStatus($successStatus);
                    }
                    $data['sco'] = $sco->getUuid();
                    $data['lessonStatus'] = $successStatus;
                    $data['completionStatus'] = $completionStatus;
                    $data['scoreMax'] = $scoreMax;
                    $data['scoreMin'] = $scoreMin;
                    $data['scoreRaw'] = $scoreRaw;
                    $data['sessionTime'] = $dataSessionTime;
                    $data['totalTime'] = $totalTimeInterval;
                    $data['result'] = $scoreRaw;
                    $data['resultMax'] = $scoreMax;
                    $event = new LogScormResultEvent($sco->getScorm(), $user, $data);
                    $this->eventDispatcher->dispatch('log', $event);

                    // Generate resource evaluation
                    $this->generateScormEvaluation(
                        $tracking,
                        $data,
                        $scoreRaw,
                        $scoreMin,
                        $scoreMax,
                        $dataSessionTime,
                        $successStatus,
                        $completionStatus
                    );
                }
                break;
        }
        $this->om->persist($tracking);
        $this->om->flush();

        return $tracking;
    }

    public function generateScormEvaluation(
        ScoTracking $tracking,
        array $data,
        $score = null,
        $scoreMin = null,
        $scoreMax = null,
        $sessionTime = null,
        $successStatus = null,
        $completionStatus = null
    ) {
        $scorm = $tracking->getSco()->getScorm();

        switch ($scorm->getVersion()) {
            case Scorm::SCORM_12:
                $duration = $sessionTime;

                switch ($successStatus) {
                    case 'passed':
                    case 'failed':
                    case 'completed':
                    case 'incomplete':
                        $status = $successStatus;
                        break;
                    case 'not attempted':
                        $status = 'not_attempted';
                        break;
                    case 'browsed':
                        $status = 'opened';
                        break;
                    default:
                        $status = 'unknown';
                }
                break;
            case Scorm::SCORM_2004:
                if (!is_null($sessionTime)) {
                    $time = new \DateInterval($sessionTime);
                    $computedTime = new \DateTime();
                    $computedTime->setTimestamp(0);
                    $computedTime->add($time);
                    $duration = $computedTime->getTimestamp();
                }
                switch ($completionStatus) {
                    case 'incomplete':
                        $status = $completionStatus;
                        break;
                    case 'completed':
                        if (in_array($successStatus, ['passed', 'failed'])) {
                            $status = $successStatus;
                        } else {
                            $status = $completionStatus;
                        }
                        break;
                    case 'not attempted':
                        $status = 'not_attempted';
                        break;
                    default:
                        $status = 'unknown';
                }
                break;
        }

        $this->resourceEvalManager->createResourceEvaluation(
            $scorm->getResourceNode(),
            $tracking->getUser(),
            $tracking->getLatestDate(),
            $status,
            $score,
            $scoreMin,
            $scoreMax,
            null,
            null,
            $duration,
            null,
            $data
        );
    }

    public function convertAllScorm12($withLogs = true)
    {
        $scormType = $this->resourceManager->getResourceTypeByName('claroline_scorm');
        $allScorm12 = $this->scorm12ResourceRepo->findAll();
        $ds = DIRECTORY_SEPARATOR;

        $this->om->startFlushSuite();
        $i = 1;

        foreach ($allScorm12 as $scorm) {
            $node = $scorm->getResourceNode();

            if ($node->isActive()) {
                $scosMapping = [];
                $newTrackings = [];
                $workspace = $node->getWorkspace();

                /* Copies ResourceNode */
                $newNode = new ResourceNode();
                $newNode->setAccessCode($node->getAccessCode());
                $newNode->setAllowedIps($node->getAllowedIps());
                $newNode->setAccessibleFrom($node->getAccessibleFrom());
                $newNode->setAccessibleUntil($node->getAccessibleUntil());
                $newNode->setAuthor($node->getAuthor());
                $newNode->setClass('Claroline\ScormBundle\Entity\Scorm');
                $newNode->setClosable($node->getClosable());
                $newNode->setCloseTarget($node->getCloseTarget());
                $newNode->setCreationDate($node->getCreationDate());
                $newNode->setCreator($node->getCreator());
                $newNode->setDescription($node->getDescription());
                $newNode->setFullscreen($node->getFullscreen());
                $newNode->setIcon($node->getIcon());
                $newNode->setIndex($node->getIndex());
                $newNode->setLicense($node->getLicense());
                $newNode->setMimeType('custom/claroline_scorm');
                $newNode->setModificationDate($node->getModificationDate());
                $newNode->setName($node->getName());
                $newNode->setParent($node->getParent());
                $newNode->setPathForCreationLog($node->getPathForCreationLog());
                $newNode->setPublished($node->isPublished());
                $newNode->setPublishedToPortal($node->isPublishedToPortal());
                $newNode->setResourceType($scormType);
                $newNode->setWorkspace($workspace);

                /* Copies rights */
                foreach ($node->getRights() as $rights) {
                    $newRights = new ResourceRights();
                    $newRights->setResourceNode($newNode);
                    $newRights->setMask($rights->getMask());
                    $newRights->setRole($rights->getRole());
                    $this->om->persist($newRights);
                }

                /* Updates shortcuts */
                $shortcuts = $this->shortcutRepo->findBy(['target' => $node]);

                foreach ($shortcuts as $shortcut) {
                    $shortcutNode = $shortcut->getResourceNode();
                    $shortcutNode->setMimeType('custom/claroline_scorm');
                    $shortcutNode->setResourceType($scormType);
                    $this->om->persist($shortcutNode);

                    $shortcut->setTarget($newNode);
                    $this->om->persist($shortcut);
                }

                $this->om->persist($newNode);

                /* Updates ResourceUserEvaluation */
                $evaluations = $this->resourceUserEvalRepo->findBy(['resourceNode' => $node]);

                foreach ($evaluations as $evaluation) {
                    $evaluation->setResourceNode($newNode);
                    $this->om->persist($evaluation);
                }

                /* Copies Scorm resource */
                $hashName = $scorm->getHashName();
                $newScorm = new Scorm();
                $newScorm->setResourceNode($newNode);
                $newScorm->setVersion(Scorm::SCORM_12);
                $newScorm->setHashName($scorm->getHashName());

                /* Copies archive file & unzipped files */
                if ($this->fileSystem->exists($this->filesDir.$ds.$hashName)) {
                    $this->fileSystem->copy(
                        $this->filesDir.$ds.$hashName,
                        $this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName
                    );
                }
                if ($this->fileSystem->exists($this->uploadDir.$ds.'scormresources'.$ds.$hashName)) {
                    $this->fileSystem->mirror(
                        $this->uploadDir.$ds.'scormresources'.$ds.$hashName,
                        $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName
                    );
                }

                /* Copies Scos & creates an array to keep associations */
                foreach ($scorm->getScos() as $sco) {
                    $newSco = new Sco();
                    $newSco->setScorm($newScorm);
                    $newSco->setEntryUrl($sco->getEntryUrl());
                    $newSco->setIdentifier($sco->getIdentifier());
                    $newSco->setTitle($sco->getTitle());
                    $newSco->setVisible($sco->isVisible());
                    $newSco->setParameters($sco->getParameters());
                    $newSco->setPrerequisites($sco->getPrerequisites());
                    $newSco->setMaxTimeAllowed($sco->getMaxTimeAllowed());
                    $newSco->setTimeLimitAction($sco->getTimeLimitAction());
                    $newSco->setLaunchData($sco->getLaunchData());
                    $newSco->setScoreToPassInt($sco->getMasteryScore());
                    $newSco->setBlock($sco->getIsBlock());
                    $this->om->persist($newSco);

                    $scosMapping[$sco->getId()] = $newSco;
                }
                /* Maps new Scos parent */
                foreach ($scorm->getScos() as $sco) {
                    $scoParent = $sco->getScoParent();

                    if (!empty($scoParent)) {
                        $newScoParent = $scosMapping[$scoParent->getId()];
                        $scosMapping[$sco->getId()]->setScoParent($newScoParent);
                        $this->om->persist($scosMapping[$sco->getId()]);
                    }
                }

                /* Copies Scos Trackings */
                foreach ($scorm->getScos() as $sco) {
                    $scoId = $sco->getId();
                    $trackings = $this->scorm12ScoTrackingRepo->findBy(['sco' => $sco]);

                    foreach ($trackings as $tracking) {
                        $trackingUser = $tracking->getUser();
                        $newTracking = new ScoTracking();
                        $newTracking->setSco($scosMapping[$scoId]);
                        $newTracking->setUser($trackingUser);
                        $newTracking->setScoreRaw($tracking->getBestScoreRaw());
                        $newTracking->setScoreMin($tracking->getScoreMin());
                        $newTracking->setScoreMax($tracking->getScoreMax());
                        $newTracking->setLessonStatus($tracking->getBestLessonStatus());
                        $newTracking->setSessionTime($tracking->getSessionTime());
                        $newTracking->setTotalTimeInt($tracking->getTotalTime());
                        $newTracking->setEntry($tracking->getEntry());
                        $newTracking->setSuspendData($tracking->getSuspendData());
                        $newTracking->setCredit($tracking->getCredit());
                        $newTracking->setExitMode($tracking->getExitMode());
                        $newTracking->setLessonLocation($tracking->getLessonLocation());
                        $newTracking->setLessonMode($tracking->getLessonMode());
                        $newTracking->setIsLocked($tracking->getIsLocked());
                        $this->om->persist($newTracking);

                        if (!isset($newTrackings[$scoId])) {
                            $newTrackings[$scoId] = [];
                        }
                        $newTrackings[$scoId][$trackingUser->getUuid()] = $newTracking;
                    }
                }

                if ($withLogs) {
                    /* Updates logs */
                    foreach ($node->getLogs()->toArray() as $log) {
                        $log->setResourceNode($newNode);
                        $log->setResourceType($scormType);

                        if ('resource-scorm_12-sco_result' === $log->getAction()) {
                            $log->setAction(LogScormResultEvent::ACTION);

                            /* Computes latest date from results logs */
                            $logDetails = $log->getDetails();
                            $logScoId = isset($logDetails['scoId']) ? $logDetails['scoId'] : null;
                            $logReceiverUuid = $log->getReceiver()->getUuid();

                            if (!empty($logScoId) && isset($newTrackings[$logScoId][$logReceiverUuid])) {
                                $logDate = $log->getDateLog();
                                $latestDate = $newTrackings[$logScoId][$logReceiverUuid]->getLatestDate();

                                if (empty($latestDate) || $logDate > $latestDate) {
                                    $newTrackings[$logScoId][$logReceiverUuid]->setLatestDate($logDate);
                                    $this->om->persist($newTrackings[$logScoId][$logReceiverUuid]);
                                }
                            }
                        }
                        $this->om->persist($log);
                    }
                }

                $this->om->persist($newScorm);

                /* Soft deletes old resource node */
                $node->setActive(false);
                $this->om->persist($node);

                if (0 === $i % 20) {
                    $this->om->forceFlush();
                }
                ++$i;
            }
        }
        $this->om->endFlushSuite();
    }

    public function convertAllScorm2004($withLogs = true)
    {
        $scormType = $this->resourceManager->getResourceTypeByName('claroline_scorm');
        $allScorm2004 = $this->scorm2004ResourceRepo->findAll();
        $ds = DIRECTORY_SEPARATOR;

        $this->om->startFlushSuite();
        $i = 1;

        foreach ($allScorm2004 as $scorm) {
            $node = $scorm->getResourceNode();

            if ($node->isActive()) {
                $scosMapping = [];
                $newTrackings = [];
                $workspace = $node->getWorkspace();

                /* Copies ResourceNode */
                $newNode = new ResourceNode();
                $newNode->setAccessCode($node->getAccessCode());
                $newNode->setAllowedIps($node->getAllowedIps());
                $newNode->setAccessibleFrom($node->getAccessibleFrom());
                $newNode->setAccessibleUntil($node->getAccessibleUntil());
                $newNode->setAuthor($node->getAuthor());
                $newNode->setClass('Claroline\ScormBundle\Entity\Scorm');
                $newNode->setClosable($node->getClosable());
                $newNode->setCloseTarget($node->getCloseTarget());
                $newNode->setCreationDate($node->getCreationDate());
                $newNode->setCreator($node->getCreator());
                $newNode->setDescription($node->getDescription());
                $newNode->setFullscreen($node->getFullscreen());
                $newNode->setIcon($node->getIcon());
                $newNode->setIndex($node->getIndex());
                $newNode->setLicense($node->getLicense());
                $newNode->setMimeType('custom/claroline_scorm');
                $newNode->setModificationDate($node->getModificationDate());
                $newNode->setName($node->getName());
                $newNode->setParent($node->getParent());
                $newNode->setPathForCreationLog($node->getPathForCreationLog());
                $newNode->setPublished($node->isPublished());
                $newNode->setPublishedToPortal($node->isPublishedToPortal());
                $newNode->setResourceType($scormType);
                $newNode->setWorkspace($workspace);

                /* Copies rights */
                foreach ($node->getRights() as $rights) {
                    $newRights = new ResourceRights();
                    $newRights->setResourceNode($newNode);
                    $newRights->setMask($rights->getMask());
                    $newRights->setRole($rights->getRole());
                    $this->om->persist($newRights);
                }

                /* Updates shortcuts */
                $shortcuts = $this->shortcutRepo->findBy(['target' => $node]);

                foreach ($shortcuts as $shortcut) {
                    $shortcutNode = $shortcut->getResourceNode();
                    $shortcutNode->setMimeType('custom/claroline_scorm');
                    $shortcutNode->setResourceType($scormType);
                    $this->om->persist($shortcutNode);

                    $shortcut->setTarget($newNode);
                    $this->om->persist($shortcut);
                }

                $this->om->persist($newNode);

                /* Updates ResourceUserEvaluation */
                $evaluations = $this->resourceUserEvalRepo->findBy(['resourceNode' => $node]);

                foreach ($evaluations as $evaluation) {
                    $evaluation->setResourceNode($newNode);
                    $this->om->persist($evaluation);
                }

                /* Copies Scorm resource */
                $hashName = $scorm->getHashName();
                $newScorm = new Scorm();
                $newScorm->setResourceNode($newNode);
                $newScorm->setVersion(Scorm::SCORM_2004);
                $newScorm->setHashName($hashName);

                /* Copies archive file & unzipped files */
                if ($this->fileSystem->exists($this->filesDir.$ds.$hashName)) {
                    $this->fileSystem->copy(
                        $this->filesDir.$ds.$hashName,
                        $this->filesDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName
                    );
                }
                if ($this->fileSystem->exists($this->uploadDir.$ds.'scormresources'.$ds.$hashName)) {
                    $this->fileSystem->mirror(
                        $this->uploadDir.$ds.'scormresources'.$ds.$hashName,
                        $this->uploadDir.$ds.'scorm'.$ds.$workspace->getUuid().$ds.$hashName
                    );
                }

                /* Copies Scos & creates an array to keep associations */
                foreach ($scorm->getScos() as $sco) {
                    $newSco = new Sco();
                    $newSco->setScorm($newScorm);
                    $newSco->setEntryUrl($sco->getEntryUrl());
                    $newSco->setIdentifier($sco->getIdentifier());
                    $newSco->setTitle($sco->getTitle());
                    $newSco->setVisible($sco->isVisible());
                    $newSco->setParameters($sco->getParameters());
                    $newSco->setMaxTimeAllowed($sco->getMaxTimeAllowed());
                    $newSco->setTimeLimitAction($sco->getTimeLimitAction());
                    $newSco->setLaunchData($sco->getLaunchData());
                    $newSco->setScoreToPassDecimal($sco->getScaledPassingScore());
                    $newSco->setCompletionThreshold($sco->getCompletionThreshold());
                    $newSco->setBlock($sco->getIsBlock());
                    $this->om->persist($newSco);

                    $scosMapping[$sco->getId()] = $newSco;
                }
                /* Maps new Scos parent */
                foreach ($scorm->getScos() as $sco) {
                    $scoParent = $sco->getScoParent();

                    if (!empty($scoParent)) {
                        $newScoParent = $scosMapping[$scoParent->getId()];
                        $scosMapping[$sco->getId()]->setScoParent($newScoParent);
                        $this->om->persist($scosMapping[$sco->getId()]);
                    }
                }

                /* Copies Scos Trackings */
                foreach ($scorm->getScos() as $sco) {
                    $trackings = $this->scorm2004ScoTrackingRepo->findBy(['sco' => $sco]);

                    foreach ($trackings as $tracking) {
                        $trackingUser = $tracking->getUser();
                        $newTracking = new ScoTracking();
                        $newTracking->setSco($scosMapping[$sco->getId()]);
                        $newTracking->setUser($trackingUser);
                        $newTracking->setScoreRaw($tracking->getScoreRaw());
                        $newTracking->setScoreMin($tracking->getScoreMin());
                        $newTracking->setScoreMax($tracking->getScoreMax());
                        $newTracking->setScoreScaled($tracking->getScoreScaled());
                        $newTracking->setCompletionStatus($tracking->getCompletionStatus());
                        $newTracking->setLessonStatus($tracking->getSuccessStatus());
                        $newTracking->setTotalTimeString($tracking->getTotalTime());
                        $newTracking->setDetails($tracking->getDetails());
                        $this->om->persist($newTracking);

                        $trackingUserUuid = $trackingUser->getUuid();

                        if (!isset($newTrackings[$trackingUserUuid])) {
                            $newTrackings[$trackingUserUuid] = [];
                        }
                        $newTrackings[$trackingUserUuid][] = $newTracking;
                    }
                }

                if ($withLogs) {
                    /* Updates logs */
                    foreach ($node->getLogs()->toArray() as $log) {
                        $log->setResourceNode($newNode);
                        $log->setResourceType($scormType);

                        if ('resource-scorm_2004-sco_result' === $log->getAction()) {
                            $log->setAction(LogScormResultEvent::ACTION);

                            /* Computes latest date from results logs */
                            $logReceiverUuid = $log->getReceiver()->getUuid();

                            if (isset($newTrackings[$logReceiverUuid]) && 0 < count($newTrackings[$logReceiverUuid])) {
                                $logDate = $log->getDateLog();
                                $latestDate = $newTrackings[$logReceiverUuid][0]->getLatestDate();

                                if (empty($latestDate) || $logDate > $latestDate) {
                                    foreach ($newTrackings[$logReceiverUuid] as $newTracking) {
                                        $newTracking->setLatestDate($logDate);
                                        $this->om->persist($newTracking);
                                    }
                                }
                            }
                        }
                        $this->om->persist($log);
                    }
                }

                $this->om->persist($newScorm);

                /* Soft deletes old resource node */
                $node->setActive(false);
                $this->om->persist($node);

                if (0 === $i % 20) {
                    $this->om->forceFlush();
                }
                ++$i;
            }
        }
        $this->om->endFlushSuite();
    }

    /**
     * Unzip a given ZIP file into the web resources directory.
     *
     * @param Workspace    $workspace
     * @param UploadedFile $file
     * @param string       $hashName  name of the destination directory
     */
    private function unzipScormArchive(Workspace $workspace, UploadedFile $file, $hashName)
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

    public function formatSessionTime($sessionTime)
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

    /***********************************
     * Access to LogRepository methods *
     ***********************************/

    /**
     * @param User         $user
     * @param ResourceNode $resourceNode
     * @param Sco          $sco
     *
     * @return \DateTime|null
     */
    public function getScoLastSessionDate(User $user, ResourceNode $resourceNode, Sco $sco)
    {
        $lastSessionDate = null;

        $logs = $this->logRepo->findBy(
            ['action' => 'resource-scorm-sco_result', 'receiver' => $user, 'resourceNode' => $resourceNode],
            ['dateLog' => 'desc']
        );

        foreach ($logs as $log) {
            $details = $log->getDetails();

            if (!isset($details['scoId']) && !isset($details['sco']) ||
                intval($details['scoId']) === $sco->getId() ||
                $details['sco'] === $sco->getUuid()
            ) {
                $lastSessionDate = $log->getDateLog();
                break;
            }
        }

        return $lastSessionDate;
    }

    public function getScormTrackingDetails(User $user, ResourceNode $resourceNode)
    {
        return $this->logRepo->findBy(
            ['action' => 'resource-scorm-sco_result', 'receiver' => $user, 'resourceNode' => $resourceNode],
            ['dateLog' => 'desc']
        );
    }
}
