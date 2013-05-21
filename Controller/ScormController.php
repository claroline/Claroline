<?php

namespace Claroline\ScormBundle\Controller;

use Claroline\ScormBundle\Entity\ScormInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
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

        // Convert sessionTime & totalTime to integer (hundredth of second)
        // Then compute the new totalTime value by adding it the sessionTime
        $sessionTimeInArray = explode(':', $sessionTime);
        $sessionTimeInArraySec = explode('.', $sessionTimeInArray[2]);
        $sessionTimeInHundredth = 0;

        if (isset($sessionTimeInArraySec[1])) {

            if (strlen($sessionTimeInArraySec[1]) === 1) {
                $sessionTimeInArraySec[1] .= "0";
            }
            $sessionTimeInHundredth = intval($sessionTimeInArraySec[1]);
        }
        $sessionTimeInHundredth += intval($sessionTimeInArraySec[0]) * 100;
        $sessionTimeInHundredth += intval($sessionTimeInArray[1]) * 6000;
        $sessionTimeInHundredth += intval($sessionTimeInArray[0]) * 144000;

        $totalTimeInArray = explode(':', $totalTime);
        $totalTimeInArraySec = explode('.', $totalTimeInArray[2]);
        $totalTimeInHundredth = 0;

        if (isset($totalTimeInArraySec[1])) {

            if (strlen($totalTimeInArraySec[1]) === 1) {
                $totalTimeInArraySec[1] .= "0";
            }
            $totalTimeInHundredth = intval($totalTimeInArraySec[1]);
        }
        $totalTimeInHundredth += intval($totalTimeInArraySec[0]) * 100;
        $totalTimeInHundredth += intval($totalTimeInArray[1]) * 6000;
        $totalTimeInHundredth += intval($totalTimeInArray[0]) * 144000;

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

        return new Response('','204');
    }
}