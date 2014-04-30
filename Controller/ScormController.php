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
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ScormBundle\Entity\ScormInfo;
use Claroline\ScormBundle\Entity\Scorm;
use Claroline\ScormBundle\Event\Log\LogScormResultEvent;
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
    private $scormInfoRepo;
    private $scormRepo;
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
        $this->scormInfoRepo = $om->getRepository('ClarolineScormBundle:ScormInfo');
        $this->scormRepo = $om->getRepository('ClarolineScormBundle:Scorm');
        $this->userRepo = $om->getRepository('ClarolineCoreBundle:User');
    }

    /**
     * @EXT\Route(
     *     "/scorm/info/commit/{datasString}/mode/{mode}",
     *     name = "claro_scorm_info_commit",
     *     options={"expose"=true}
     * )
     *
     * @param string $datasString
     * @param string $mode  determines if given datas must be persisted
     *                      or logged
     *
     * @return Response
     */
    public function commitScormInfo($datasString, $mode)
    {
        $datasArray = explode("<-;->", $datasString);
        $scormId = intval($datasArray[0]);
        $studentId = intval($datasArray[1]);
        $lessonMode = $datasArray[2];
        $lessonLocation = $datasArray[3];
        $lessonStatus = $datasArray[4];
        $credit = $datasArray[5];
        $scoreRaw = intval($datasArray[6]);
        $scoreMin = intval($datasArray[7]);
        $scoreMax = intval($datasArray[8]);
        $sessionTime = $datasArray[9];
        $totalTime = $datasArray[10];
        $suspendData = $datasArray[11];
        $entry = $datasArray[12];
        $exitMode = $datasArray[13];

        $user = $this->securityContext->getToken()->getUser();

        if ($user->getId() !== $studentId) {
            throw new AccessDeniedException();
        }
        $scorm = $this->scormRepo->findOneById($scormId);

        $sessionTimeInHundredth = $this->convertTimeInHundredth($sessionTime);
        $totalTimeInHundredth = $this->convertTimeInHundredth($totalTime);
        $totalTimeInHundredth += $sessionTimeInHundredth;

        if ($mode === 'persist') {
            $this->persistScormInfo(
                $scorm,
                $user,
                $credit,
                $entry,
                $exitMode,
                $lessonLocation,
                $lessonMode,
                $lessonStatus,
                $scoreMax,
                $scoreMin,
                $scoreRaw,
                $sessionTimeInHundredth,
                $suspendData,
                $totalTimeInHundredth
            );
        } elseif ($mode === 'log') {
            $this->logScormResult(
                $scorm,
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
                $totalTimeInHundredth
            );
        }

        return new Response('', '204');
    }

    /**
     * Persist given datas in Scorm informations
     */
    private function persistScormInfo(
        Scorm $scorm,
        User $user,
        $credit,
        $entry,
        $exitMode,
        $lessonLocation,
        $lessonMode,
        $lessonStatus,
        $scoreMax,
        $scoreMin,
        $scoreRaw,
        $sessionTimeInHundredth,
        $suspendData,
        $totalTimeInHundredth
    )
    {
        $scormInfo = $this->scormInfoRepo->findOneBy(
            array('user' => $user->getId(), 'scorm' => $scorm->getId())
        );

        if (is_null($scormInfo)) {
            $scormInfo = new ScormInfo();
            $scormInfo->setCredit($credit);
            $scormInfo->setLessonMode($lessonMode);
            $scormInfo->setScorm($scorm);
            $scormInfo->setUser($user);
        }

        $scormInfo->setEntry($entry);
        $scormInfo->setExitMode($exitMode);
        $scormInfo->setLessonLocation($lessonLocation);
        $scormInfo->setLessonStatus($lessonStatus);
        $scormInfo->setScoreMax($scoreMax);
        $scormInfo->setScoreMin($scoreMin);
        $scormInfo->setScoreRaw($scoreRaw);
        $scormInfo->setSessionTime($sessionTimeInHundredth);
        $scormInfo->setSuspendData($suspendData);
        $scormInfo->setTotalTime($totalTimeInHundredth);

        $this->om->persist($scormInfo);
        $this->om->flush();
    }

    /**
     * Log given datas as result of a Scorm resource
     */
    private function logScormResult(
        Scorm $scorm,
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
        $totalTimeInHundredth
    )
    {
        $details = array();
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

        $log = new LogScormResultEvent(
            "resource_scorm_result",
            $details,
            null,
            null,
            $scorm->getResourceNode(),
            null,
            $scorm->getResourceNode()->getWorkspace(),
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
}