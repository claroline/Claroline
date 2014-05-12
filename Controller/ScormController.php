<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ScormBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Resource\ResourceCollection;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ScormBundle\Entity\Scorm12Resource;
use Claroline\ScormBundle\Entity\Scorm12Sco;
use Claroline\ScormBundle\Entity\Scorm12ScoTracking;
use Claroline\ScormBundle\Event\Log\LogScorm12ResultEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use JMS\DiExtraBundle\Annotation as DI;

class ScormController extends Controller
{
    private $eventDispatcher;
    private $om;
    private $securityContext;
    private $scorm12ScoTrackingRepo;
    private $userRepo;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher"    = @DI\Inject("event_dispatcher"),
     *     "om"                 = @DI\Inject("claroline.persistence.object_manager"),
     *     "securityContext"    = @DI\Inject("security.context")
     * })
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ObjectManager $om,
        SecurityContextInterface $securityContext
    )
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->om = $om;
        $this->securityContext = $securityContext;
        $this->scorm12ScoTrackingRepo = $om->getRepository('ClarolineScormBundle:Scorm12ScoTracking');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
    }

    /**
     * @EXT\Route(
     *     "/render/scorm/12/{scormId}",
     *     name = "claro_render_scorm_12_resource"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "scorm",
     *      class="ClarolineScormBundle:Scorm12Resource",
     *      options={"id" = "scormId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineScormBundle::scorm12.html.twig")
     *
     * @param Scorm12Resource $scorm
     *
     * @return Response
     */
    public function renderScorm12ResourceAction(Scorm12Resource $scorm)
    {
        $this->checkAccess('OPEN', $scorm);
        $user = $this->securityContext->getToken()->getUser();

        $scos = $scorm->getScos();
        $rootScos = array();

        $checkTracking = true;
        $createTracking = false;

        foreach ($scos as $sco) {
            if (is_null($sco->getScoParent())) {
                $rootScos[] = $sco;
            }

            if ($checkTracking) {
                $scoTracking = $this->scorm12ScoTrackingRepo->findOneBy(
                    array('user' => $user->getId(), 'sco' => $sco->getId())
                );
                $checkTracking = false;

                if (is_null($scoTracking)) {
                    $createTracking = true;
                }
            }

            if ($createTracking) {
                $scoTracking = new Scorm12ScoTracking();
                $scoTracking->setUser($user);
                $scoTracking->setSco($sco);
                $scoTracking->setScoreRaw(-1);
                $scoTracking->setScoreMax(-1);
                $scoTracking->setScoreMin(-1);
                $scoTracking->setLessonStatus('not attempted');
                $scoTracking->setSuspendData('');
                $scoTracking->setEntry('ab-initio');
                $scoTracking->setLessonLocation('');
                $scoTracking->setCredit('no-credit');
                $scoTracking->setTotalTime(0);
                $scoTracking->setSessionTime(0);
                $scoTracking->setLessonMode('normal');
                $scoTracking->setExitMode('');
                $scoTracking->setBestLessonStatus('not attempted');

                if (is_null($sco->getPrerequisites())) {
                    $scoTracking->setIsLocked(false);
                } else {
                    $scoTracking->setIsLocked(true);
                }
                $this->om->persist($scoTracking);
            }
        }

        if ($createTracking) {
            $this->om->flush();
        }

        return array(
            'resource' => $scorm,
            '_resource' => $scorm,
            'scos' => $rootScos,
            'workspace' => $scorm->getResourceNode()->getWorkspace()
        );
    }

    /**
     * @EXT\Route(
     *     "/scorm/12/render/sco/{scoId}",
     *     name = "claro_render_scorm_12_sco"
     * )
     * @EXT\Method("GET")
     * @EXT\ParamConverter(
     *      "scorm12Sco",
     *      class="ClarolineScormBundle:Scorm12Sco",
     *      options={"id" = "scoId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineScormBundle::scorm12MenuSco.html.twig")
     *
     * @param Scorm12Sco $scorm12Sco
     *
     * @return Response
     */
    public function renderScorm12ScoAction(Scorm12Sco $scorm12Sco)
    {
        $user = $this->securityContext->getToken()->getUser();
        $scorm = $scorm12Sco->getScormResource();
        $this->checkAccess('OPEN', $scorm);

        $scos = $scorm->getScos();
        $entryUrl = $scorm12Sco->getEntryUrl();

        if (is_string($entryUrl) && preg_match('/^http/', $entryUrl)) {
            $scormPath = $entryUrl . $scorm12Sco->getParameters();
        } else {
            $scormPath = 'uploads/scormresources/'
                . $scorm->getHashName()
                . DIRECTORY_SEPARATOR
                . $scorm12Sco->getEntryUrl()
                . $scorm12Sco->getParameters();
        }
        $rootScos = array();

        foreach ($scos as $sco) {
            if (is_null($sco->getScoParent())) {
                $rootScos[] = $sco;
            }
        }
        $scoTracking = $this->scorm12ScoTrackingRepo->findOneBy(
            array('user' => $user->getId(), 'sco' => $scorm12Sco->getId())
        );

        return array(
            'resource' => $scorm,
            '_resource' => $scorm,
            'currentSco' => $scorm12Sco,
            'scos' => $rootScos,
            'scoTracking' => $scoTracking,
            'scormUrl' => $scormPath,
            'workspace' => $scorm->getResourceNode()->getWorkspace()
        );
    }

    /**
     * @EXT\Route(
     *     "/scorm/tracking/commit/{datasString}/mode/{mode}/sco/{scoId}",
     *     name = "claro_scorm_12_tracking_commit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "scorm12Sco",
     *      class="ClarolineScormBundle:Scorm12Sco",
     *      options={"id" = "scoId", "strictId" = true}
     * )
     *
     * @param string $datasString
     * @param string $mode  determines if given datas must be persisted
     *                      or logged
     *
     * @return Response
     */
    public function commitScorm12Tracking(
        $datasString,
        $mode,
        Scorm12Sco $scorm12Sco
    )
    {
        $datasArray = explode("<-;->", $datasString);
        $studentId = intval($datasArray[0]);
        $lessonMode = $datasArray[1];
        $lessonLocation = $datasArray[2];
        $lessonStatus = $datasArray[3];
        $credit = $datasArray[4];
        $scoreRaw = intval($datasArray[5]);
        $scoreMin = intval($datasArray[6]);
        $scoreMax = intval($datasArray[7]);
        $sessionTime = $datasArray[8];
        $totalTime = $datasArray[9];
        $suspendData = $datasArray[10];
        $entry = $datasArray[11];
        $exitMode = $datasArray[12];

        $user = $this->securityContext->getToken()->getUser();

        if ($user->getId() !== $studentId) {
            throw new AccessDeniedException();
        }
        $sessionTimeInHundredth = $this->convertTimeInHundredth($sessionTime);

        $scoTracking = $this->scorm12ScoTrackingRepo->findOneBy(
            array('user' => $user->getId(), 'sco' => $scorm12Sco->getId())
        );

        $scoTracking->setEntry($entry);
        $scoTracking->setExitMode($exitMode);
        $scoTracking->setLessonLocation($lessonLocation);
        $scoTracking->setLessonStatus($lessonStatus);
        $scoTracking->setScoreMax($scoreMax);
        $scoTracking->setScoreMin($scoreMin);
        $scoTracking->setScoreRaw($scoreRaw);
        $scoTracking->setSessionTime($sessionTimeInHundredth);
        $scoTracking->setSuspendData($suspendData);

        if ($mode === 'log') {
            // Compute total time
            $totalTimeInHundredth = $this->convertTimeInHundredth($totalTime);
            $totalTimeInHundredth += $sessionTimeInHundredth;
            // Persist total time
            $scoTracking->setTotalTime($totalTimeInHundredth);

            $bestScore = $scoTracking->getBestScoreRaw();
            $bestStatus = $scoTracking->getBestLessonStatus();

            // Update best score if the current score is better than the previous best score
            if (empty($bestScore) || (!is_null($scoreRaw) && $scoreRaw > $bestScore)) {
                $scoTracking->setBestScoreRaw($scoreRaw);
                $bestScore = $scoreRaw;
            }
            // Update best lesson status if :
            // - current best status = 'not attempted'
            // - current best status = 'browsed' or 'incomplete'
            //   and current status = 'failed' or 'passed' or 'completed'
            // - current best status = 'failed'
            //   and current status = 'passed' or 'completed'
            if ($lessonStatus !== $bestStatus
                && $bestStatus !== 'passed'
                && $bestStatus !== 'completed') {

                if (($bestStatus === 'not attempted' && !empty($lessonStatus))
                    || (($bestStatus === 'browsed' || $bestStatus === 'incomplete')
                        && ($lessonStatus === 'failed' || $lessonStatus === 'passed' || $lessonStatus === 'completed'))
                    || ($bestStatus === 'failed' && ($lessonStatus === 'passed' || $lessonStatus === 'completed'))) {

                    $scoTracking->setBestLessonStatus($lessonStatus);
                    $bestStatus = $lessonStatus;
                }
            }

            $this->logScorm12ScoResult(
                $scorm12Sco,
                $user,
                $credit,
                $exitMode,
                $lessonMode,
                $lessonStatus,
                $scoreMax,
                $scoreMin,
                $scoreRaw,
                $sessionTimeInHundredth,
                $suspendData,
                $totalTimeInHundredth,
                $bestScore,
                $bestStatus
            );
        }

        $this->om->persist($scoTracking);
        $this->om->flush();

        return new Response('', '204');
    }

    /**
     * Log given datas as result of a Scorm resource
     */
    private function logScorm12ScoResult(
        Scorm12Sco $sco,
        User $user,
        $credit,
        $exitMode,
        $lessonMode,
        $lessonStatus,
        $scoreMax,
        $scoreMin,
        $scoreRaw,
        $sessionTimeInHundredth,
        $suspendData,
        $totalTimeInHundredth,
        $bestScore,
        $bestStatus
    )
    {
        $scormResource = $sco->getScormResource();
        $details = array();
        $details['scoId'] = $sco->getId();
        $details['credit'] = $credit;
        $details['exitMode'] = $exitMode;
        $details['lessonMode'] = $lessonMode;
        $details['lessonStatus'] = $lessonStatus;
        $details['scoreMax'] = $scoreMax;
        $details['scoreMin'] = $scoreMin;
        $details['scoreRaw'] = $scoreRaw;
        $details['sessionTime'] = $sessionTimeInHundredth;
        $details['suspendData'] = $suspendData;
        $details['totalTime'] = $totalTimeInHundredth;
        $details['totalTime'] = $totalTimeInHundredth;
        $details['bestScore'] = $bestScore;
        $details['bestStatus'] = $bestStatus;

        $log = new LogScorm12ResultEvent(
            "resource_scorm_12_sco_result",
            $details,
            null,
            null,
            $scormResource->getResourceNode(),
            null,
            $scormResource->getResourceNode()->getWorkspace(),
            $user,
            null,
            null,
            null
        );
        $this->eventDispatcher->dispatch('log', $log);
    }

    /**
     * Convert time (HHHH:MM:SS.hh) to integer (hundredth of second)
     *
     * @param string $time
     */
    private function convertTimeInHundredth($time) {
        $timeInArray = explode(':', $time);
        $timeInArraySec = explode('.', $timeInArray[2]);
        $timeInHundredth = 0;

        if (isset($timeInArraySec[1])) {

            if (strlen($timeInArraySec[1]) === 1) {
                $timeInArraySec[1] .= "0";
            }
            $timeInHundredth = intval($timeInArraySec[1]);
        }
        $timeInHundredth += intval($timeInArraySec[0]) * 100;
        $timeInHundredth += intval($timeInArray[1]) * 6000;
        $timeInHundredth += intval($timeInArray[0]) * 144000;

        return $timeInHundredth;
    }

    /**
     * Checks if the current user has the right to perform an action on a Scorm12Resource.
     *
     * @param string $permission
     * @param Scorm12Resource $resource
     *
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, Scorm12Resource $resource)
    {
        $collection = new ResourceCollection(array($resource->getResourceNode()));

        if (!$this->securityContext->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }
}