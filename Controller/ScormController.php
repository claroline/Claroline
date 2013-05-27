<?php

namespace Claroline\ScormBundle\Controller;

use Claroline\ScormBundle\Entity\ScormInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Claroline\CoreBundle\Library\Event\LogGenericEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ScormController extends Controller
{
    /**
     * @Route(
     *     "/scorm/info/commit/{datasString}",
     *     name = "claro_scorm_info_commit",
     *     options={"expose"=true}
     * )
     *
     * @param string $datasString
     *
     * @return Response
     */
    public function commitScormInfo($datasString)
    {
        $datasArray = explode("<-;->", $datasString);
        $scormId = $datasArray[0];
        $studentId = $datasArray[1];
        $lessonMode = $datasArray[2];
        $lessonLocation = $datasArray[3];
        $lessonStatus = $datasArray[4];
        $credit = $datasArray[5];
        $scoreRaw = $datasArray[6];
        $scoreMin = $datasArray[7];
        $scoreMax = $datasArray[8];
        $sessionTime = $datasArray[9];
        $totalTime = $datasArray[10];
        $suspendData = $datasArray[11];
        $entry = $datasArray[12];
        $exitMode = $datasArray[13];

        if ($this->get('security.context')->getToken()->getUser()->getId() !== intval($studentId)) {
            throw new AccessDeniedException();
        }

        $sessionTimeInHundredth = $this->convertTimeInHundredth($sessionTime);
        $totalTimeInHundredth = $this->convertTimeInHundredth($totalTime);
        $totalTimeInHundredth += $sessionTimeInHundredth;

        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('ClarolineCoreBundle:User')
            ->findOneById(intval($studentId));
        $scorm = $em->getRepository('ClarolineScormBundle:Scorm')
            ->findOneById(intval($scormId));
        $scormInfo = $em->getRepository('ClarolineScormBundle:ScormInfo')
            ->findOneBy(array('user' => $user->getId(), 'scorm' => $scorm->getId()));

        if (is_null($scormInfo)) {
            $scormInfo = new ScormInfo();
            $scormInfo->setUser($user);
            $scormInfo->setScorm($scorm);
            $scormInfo->setLessonMode($lessonMode);
            $scormInfo->setCredit($credit);
        }

        $scormInfo->setLessonLocation($lessonLocation);
        $scormInfo->setLessonStatus($lessonStatus);
        $scormInfo->setScoreRaw(intval($scoreRaw));
        $scormInfo->setScoreMin(intval($scoreMin));
        $scormInfo->setScoreMax(intval($scoreMax));
        $scormInfo->setSessionTime($sessionTimeInHundredth);
        $scormInfo->setTotalTime($totalTimeInHundredth);
        $scormInfo->setEntry($entry);
        $scormInfo->setExitMode($exitMode);
        $scormInfo->setSuspendData($suspendData);

        $em->persist($scormInfo);
        $em->flush();

        $details = array();
        $details['scoreRaw'] = $scormInfo->getScoreRaw();
        $details['scoreMin'] = $scormInfo->getScoreMin();
        $details['scoreMax'] = $scormInfo->getScoreMax();
        $details['lessonStatus'] = $scormInfo->getLessonStatus();
        $details['sessionTime'] = $scormInfo->getSessionTime();
        $details['totalTime'] = $scormInfo->getTotalTime();
        $details['suspendData'] = $scormInfo->getSuspendData();
        $details['exitMode'] = $scormInfo->getExitMode();
        $details['credit'] = $scormInfo->getCredit();
        $details['lessonMode'] = $scormInfo->getLessonMode();

        $log = new LogGenericEvent(
            "resource_scorm_result",
            $details, null, null,
            $scorm, null, $scorm->getWorkspace(),
            $user, null, null, null
        );
        $this->get('event_dispatcher')->dispatch('log', $log);

        return new Response('', '204');
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