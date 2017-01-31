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
use Claroline\CoreBundle\Library\Security\Collection\ResourceCollection;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\ScormBundle\Entity\Scorm12Resource;
use Claroline\ScormBundle\Entity\Scorm12Sco;
use Claroline\ScormBundle\Entity\Scorm2004Resource;
use Claroline\ScormBundle\Entity\Scorm2004Sco;
use Claroline\ScormBundle\Event\Log\LogScorm12ResultEvent;
use Claroline\ScormBundle\Event\Log\LogScorm2004ResultEvent;
use Claroline\ScormBundle\Form\ScormConfigType;
use Claroline\ScormBundle\Manager\ScormManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ScormController extends Controller
{
    private $eventDispatcher;
    private $formFactory;
    private $om;
    private $request;
    private $router;
    private $scormManager;
    private $tokenStorage;
    private $authorization;

    /**
     * @DI\InjectParams({
     *     "eventDispatcher" = @DI\Inject("event_dispatcher"),
     *     "formFactory"     = @DI\Inject("form.factory"),
     *     "om"              = @DI\Inject("claroline.persistence.object_manager"),
     *     "requestStack"    = @DI\Inject("request_stack"),
     *     "router"          = @DI\Inject("router"),
     *     "scormManager"    = @DI\Inject("claroline.manager.scorm_manager"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage")
     * })
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FormFactory $formFactory,
        ObjectManager $om,
        RequestStack $requestStack,
        RouterInterface $router,
        ScormManager $scormManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->om = $om;
        $this->request = $requestStack->getCurrentRequest();
        $this->router = $router;
        $this->scormManager = $scormManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
    }

    /**
     * @EXT\Route(
     *     "/render/scorm/12/{scormId}/mode/{mode}",
     *     name = "claro_render_scorm_12_resource",
     *     defaults={"mode"=0}
     * )
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
    public function renderScorm12ResourceAction(Scorm12Resource $scorm, $mode = 0)
    {
        $this->checkAccess('OPEN', $scorm);
        $canEdit = $this->hasScorm12Right($scorm, 'EDIT');
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = ($user === 'anon.');
        $rootScos = [];
        $trackings = [];
        $scos = $scorm->getScos();
        $nbActiveScos = 0;
        $lastActiveSco = null;

        if (!$isAnon) {
            $scosTracking = $this->scormManager->getAllScorm12ScoTrackingsByUserAndResource($user, $scorm);

            foreach ($scosTracking as $tracking) {
                $trackings[$tracking->getSco()->getId()] = $tracking;
            }
        }

        foreach ($scos as $sco) {
            if (is_null($sco->getScoParent())) {
                $rootScos[] = $sco;
            }

            if ($isAnon) {
                $trackings[$sco->getId()] = $this->scormManager->createEmptyScorm12ScoTracking($sco);
            } elseif (!isset($trackings[$sco->getId()])) {
                $trackings[$sco->getId()] = $this->scormManager->createScorm12ScoTracking($user, $sco);
            }

            if (!$sco->getIsBlock()) {
                ++$nbActiveScos;
                $lastActiveSco = $sco;
            }
        }

        if ($mode === 0 && $nbActiveScos === 1) {
            return $this->forward('ClarolineScormBundle:Scorm:renderScorm12Sco', ['scoId' => $lastActiveSco->getId()]);
        } else {
            return [
                'resource' => $scorm,
                '_resource' => $scorm,
                'scos' => $rootScos,
                'workspace' => $scorm->getResourceNode()->getWorkspace(),
                'trackings' => $trackings,
                'isAnon' => $isAnon,
                'canEdit' => $canEdit,
                'hideTopBar' => $scorm->getHideTopBar(),
            ];
        }
    }

    /**
     * @EXT\Route(
     *     "/scorm/12/render/sco/{scoId}",
     *     name = "claro_render_scorm_12_sco"
     * )
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
        $user = $this->tokenStorage->getToken()->getUser();
        $scorm = $scorm12Sco->getScormResource();
        $canEdit = $this->hasScorm12Right($scorm, 'EDIT');
        $this->checkAccess('OPEN', $scorm);
        $isAnon = ($user === 'anon.');

        $scos = $scorm->getScos();
        $entryUrl = $scorm12Sco->getEntryUrl();

        if (is_string($entryUrl) && preg_match('/^http/', $entryUrl)) {
            $scormPath = $entryUrl.$scorm12Sco->getParameters();
        } else {
            $scormPath = 'uploads/scormresources/'
                .$scorm->getHashName()
                .DIRECTORY_SEPARATOR
                .$scorm12Sco->getEntryUrl()
                .$scorm12Sco->getParameters();
        }
        $rootScos = [];

        foreach ($scos as $sco) {
            if (is_null($sco->getScoParent())) {
                $rootScos[] = $sco;
            }
        }

        if ($isAnon) {
            $scoTracking = $this->scormManager->createEmptyScorm12ScoTracking($scorm12Sco);
        } else {
            $scoTracking = $this->scormManager->getScorm12ScoTrackingByUserAndSco($user, $scorm12Sco);

            if (is_null($scoTracking)) {
                $scoTracking = $this->scormManager->createScorm12ScoTracking($user, $scorm12Sco);
            }
        }

        return [
            'resource' => $scorm,
            '_resource' => $scorm,
            'currentSco' => $scorm12Sco,
            'scos' => $rootScos,
            'scoTracking' => $scoTracking,
            'scormUrl' => $scormPath,
            'workspace' => $scorm->getResourceNode()->getWorkspace(),
            'isAnon' => $isAnon,
            'canEdit' => $canEdit,
            'hideTopBar' => $scorm->getHideTopBar(),
        ];
    }

    /**
     * @EXT\Route(
     *     "/scorm/tracking/commit/{datasString}/mode/{mode}/sco/{scoId}",
     *     name = "claro_scorm_12_tracking_commit",
     *     requirements={"datasString"=".+"},
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "scorm12Sco",
     *      class="ClarolineScormBundle:Scorm12Sco",
     *      options={"id" = "scoId", "strictId" = true}
     * )
     *
     * @param string $datasString
     * @param string $mode        determines if given datas must be persisted
     *                            or logged
     *
     * @return Response
     */
    public function commitScorm12Tracking($datasString, $mode, Scorm12Sco $scorm12Sco)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $scorm = $scorm12Sco->getScormResource();
        $this->checkAccess('OPEN', $scorm);

        if ($user === 'anon.') {
            return new Response('', '204');
        }

        $datasArray = explode('<-;->', $datasString);
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

        if ($user->getId() !== $studentId) {
            throw new AccessDeniedException();
        }
        $sessionTimeInHundredth = $this->convertTimeInHundredth($sessionTime);

        $scoTracking = $this->scormManager->getScorm12ScoTrackingByUserAndSco($user, $scorm12Sco);

        if (is_null($scoTracking)) {
            $scoTracking = $this->scormManager->createScorm12ScoTracking($user, $scorm12Sco);
        }
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
        $this->scormManager->updateScorm12ScoTracking($scoTracking);

        return new Response('', '204');
    }

    /**
     * Log given datas as result of a Scorm resource.
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
    ) {
        $scormResource = $sco->getScormResource();
        $details = [];
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

        if (!empty($scoreMax) && $scoreMax > 0) {
            $details['result'] = $scoreRaw;
            $details['resultMax'] = $scoreMax;
        }

        $event = new LogScorm12ResultEvent($scormResource, $user, $details);
        $this->eventDispatcher->dispatch('log', $event);
    }

    /**
     * @EXT\Route(
     *     "/render/scorm/2004/{scormId}/mode/{mode}",
     *     name = "claro_render_scorm_2004_resource",
     *     defaults={"mode"=0}
     * )
     * @EXT\ParamConverter(
     *      "scorm",
     *      class="ClarolineScormBundle:Scorm2004Resource",
     *      options={"id" = "scormId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineScormBundle::scorm2004.html.twig")
     *
     * @param Scorm2004Resource $scorm
     *
     * @return Response
     */
    public function renderScorm2004ResourceAction(Scorm2004Resource $scorm, $mode = 0)
    {
        $this->checkScorm2004ResourceAccess('OPEN', $scorm);
        $canEdit = $this->hasScorm2004Right($scorm, 'EDIT');
        $user = $this->tokenStorage->getToken()->getUser();
        $isAnon = ($user === 'anon.');
        $rootScos = [];
        $trackings = [];
        $scos = $scorm->getScos();
        $nbActiveScos = 0;
        $lastActiveSco = null;

        if (!$isAnon) {
            $scosTracking = $this->scormManager->getAllScorm2004ScoTrackingsByUserAndResource($user, $scorm);

            foreach ($scosTracking as $tracking) {
                $trackings[$tracking->getSco()->getId()] = $tracking;
            }
        }

        foreach ($scos as $sco) {
            if (is_null($sco->getScoParent())) {
                $rootScos[] = $sco;
            }

            if ($isAnon) {
                $trackings[$sco->getId()] = $this->scormManager->createEmptyScorm2004ScoTracking($sco);
            } elseif (!isset($trackings[$sco->getId()])) {
                $trackings[$sco->getId()] = $this->scormManager->createScorm2004ScoTracking($user, $sco);
            }

            if (!$sco->getIsBlock()) {
                ++$nbActiveScos;
                $lastActiveSco = $sco;
            }
        }

        if ($mode === 0 && $nbActiveScos === 1) {
            return $this->forward('ClarolineScormBundle:Scorm:renderScorm2004Sco', ['scoId' => $lastActiveSco->getId()]);
        } else {
            return [
                'resource' => $scorm,
                '_resource' => $scorm,
                'scos' => $rootScos,
                'workspace' => $scorm->getResourceNode()->getWorkspace(),
                'trackings' => $trackings,
                'isAnon' => $isAnon,
                'canEdit' => $canEdit,
                'hideTopBar' => $scorm->getHideTopBar(),
            ];
        }
    }

    /**
     * @EXT\Route(
     *     "/scorm/2004/render/sco/{scoId}",
     *     name = "claro_render_scorm_2004_sco"
     * )
     * @EXT\ParamConverter(
     *      "scorm2004Sco",
     *      class="ClarolineScormBundle:Scorm2004Sco",
     *      options={"id" = "scoId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineScormBundle::scorm2004MenuSco.html.twig")
     *
     * @param Scorm2004Sco $scorm2004Sco
     *
     * @return Response
     */
    public function renderScorm2004ScoAction(Scorm2004Sco $scorm2004Sco)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $scorm = $scorm2004Sco->getScormResource();
        $this->checkScorm2004ResourceAccess('OPEN', $scorm);
        $canEdit = $this->hasScorm2004Right($scorm, 'EDIT');
        $isAnon = ($user === 'anon.');

        $scos = $scorm->getScos();
        $entryUrl = $scorm2004Sco->getEntryUrl();

        if (is_string($entryUrl) && preg_match('/^http/', $entryUrl)) {
            $scormPath = $entryUrl.$scorm2004Sco->getParameters();
        } else {
            $scormPath = 'uploads/scormresources/'
                .$scorm->getHashName()
                .DIRECTORY_SEPARATOR
                .$scorm2004Sco->getEntryUrl()
                .$scorm2004Sco->getParameters();
        }
        $rootScos = [];

        foreach ($scos as $sco) {
            if (is_null($sco->getScoParent())) {
                $rootScos[] = $sco;
            }
        }

        if ($isAnon) {
            $scoTracking = $this->scormManager->createEmptyScorm2004ScoTracking($scorm2004Sco);
            $details = [];
            $details['cmi.learner_id'] = -1;
            $details['cmi.learner_name'] = 'anon., anon.';
        } else {
            $scoTracking = $this->scormManager->getScorm2004ScoTrackingByUserAndSco($user, $scorm2004Sco);

            if (is_null($scoTracking)) {
                $scoTracking = $this->scormManager->createScorm2004ScoTracking($user, $scorm2004Sco);
            }

            $details = !is_null($scoTracking->getDetails()) ? $scoTracking->getDetails() : [];
            $details['cmi.learner_id'] = $user->getId();
            $details['cmi.learner_name'] = $user->getFirstName().', '.$user->getLastName();
        }
        $timeLimitAction = $scorm2004Sco->getTimeLimitAction();
        $totalTime = $scoTracking->getTotalTime();
        $completionThreshold = $scorm2004Sco->getCompletionThreshold();
        $maxTimeAllowed = $scorm2004Sco->getMaxTimeAllowed();
        $scaledPassingScore = $scorm2004Sco->getScaledPassingScore();

        $details['cmi.time_limit_action'] = !is_null($timeLimitAction) ? $timeLimitAction : 'continue,no message';
        $details['cmi.total_time'] = !is_null($totalTime) ? $totalTime : 'PT0S';
        $details['cmi.launch_data'] = $scorm2004Sco->getLaunchData();
        $details['cmi.completion_threshold'] = !is_null($completionThreshold) ? $completionThreshold : '';
        $details['cmi.max_time_allowed'] = !is_null($maxTimeAllowed) ? $maxTimeAllowed : '';
        $details['cmi.scaled_passing_score'] = !is_null($scaledPassingScore) ? $scaledPassingScore : '';

        return [
            'resource' => $scorm,
            '_resource' => $scorm,
            'currentSco' => $scorm2004Sco,
            'scos' => $rootScos,
            'scoTracking' => $scoTracking,
            'scoTrackingDetails' => json_encode($details),
            'scormUrl' => $scormPath,
            'workspace' => $scorm->getResourceNode()->getWorkspace(),
            'isAnon' => $isAnon,
            'canEdit' => $canEdit,
            'hideTopBar' => $scorm->getHideTopBar(),
        ];
    }

    /**
     * @EXT\Route(
     *     "/scorm/2004/tracking/commit/mode/{mode}/sco/{scoId}",
     *     name = "claro_scorm_2004_tracking_commit",
     *     options={"expose"=true}
     * )
     * @EXT\ParamConverter(
     *      "scorm2004Sco",
     *      class="ClarolineScormBundle:Scorm2004Sco",
     *      options={"id" = "scoId", "strictId" = true}
     * )
     *
     * @param string       $mode         determines if given datas must be persisted
     *                                   or logged
     * @param Scorm2004Sco $scorm2004Sco
     *
     * @return Response
     */
    public function commitScorm2004Tracking($mode, Scorm2004Sco $scorm2004Sco)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        $scorm = $scorm2004Sco->getScormResource();
        $this->checkScorm2004ResourceAccess('OPEN', $scorm);

        if ($user === 'anon.') {
            return new Response('', '204');
        }
        $datas = $this->request->request->all();
        $learnerId = isset($datas['cmi.learner_id']) ? (int) $datas['cmi.learner_id'] : -1;

        if ($user->getId() !== $learnerId) {
            throw new AccessDeniedException();
        }

        $scoTracking = $this->scormManager->getScorm2004ScoTrackingByUserAndSco($user, $scorm2004Sco);

        if (is_null($scoTracking)) {
            $scoTracking = $this->scormManager->createScorm2004ScoTracking($user, $scorm2004Sco);
        }

        if ($mode === 'log') {
            $dataSessionTime = isset($datas['cmi.session_time']) ? $this->formatSessionTime($datas['cmi.session_time']) : 'PT0S';
            $completionStatus = isset($datas['cmi.completion_status']) ? $datas['cmi.completion_status'] : 'unknown';
            $successStatus = isset($datas['cmi.success_status']) ? $datas['cmi.success_status'] : 'unknown';
            $scoreRaw = isset($datas['cmi.score.raw']) ? intval($datas['cmi.score.raw']) : null;
            $scoreMin = isset($datas['cmi.score.min']) ? intval($datas['cmi.score.min']) : null;
            $scoreMax = isset($datas['cmi.score.max']) ? intval($datas['cmi.score.max']) : null;
            $scoreScaled = isset($datas['cmi.score.scaled']) ? floatval($datas['cmi.score.scaled']) : null;
            $bestScore = $scoTracking->getScoreRaw();

            // Computes total time
            $totalTime = new \DateInterval($scoTracking->getTotalTime());

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
            $datas['cmi.total_time'] = $totalTimeInterval;
            $scoTracking->setTotalTime($totalTimeInterval);

            // Update best score if the current score is better than the previous best score
            if (empty($bestScore) || (!is_null($scoreRaw) && $scoreRaw > $bestScore)) {
                $scoTracking->setScoreRaw($scoreRaw);
                $scoTracking->setScoreMin($scoreMin);
                $scoTracking->setScoreMax($scoreMax);
                $scoTracking->setScoreScaled($scoreScaled);
            }

            // Update best success status and completion status
            $currentCompletionStatus = $scoTracking->getCompletionStatus();
            $currentSuccessStatus = $scoTracking->getSuccessStatus();
            $conditionCA = ($currentCompletionStatus === 'unknown') &&
                ($completionStatus === 'completed' ||
                $completionStatus === 'incomplete' ||
                $completionStatus === 'not_attempted');
            $conditionCB = ($currentCompletionStatus === 'not_attempted') && ($completionStatus === 'completed' || $completionStatus === 'incomplete');
            $conditionCC = ($currentCompletionStatus === 'incomplete') && ($completionStatus === 'completed');
            $conditionSA = ($currentSuccessStatus === 'unknown') && ($successStatus === 'passed' || $successStatus === 'failed');
            $conditionSB = ($currentSuccessStatus === 'failed') && ($successStatus === 'passed');

            if (is_null($currentCompletionStatus) || $conditionCA || $conditionCB || $conditionCC) {
                $scoTracking->setCompletionStatus($completionStatus);
            }

            if (is_null($currentSuccessStatus) || $conditionSA || $conditionSB) {
                $scoTracking->setSuccessStatus($successStatus);
            }
            $datas['scoId'] = $scorm2004Sco->getId();

            $this->logScorm2004ScoResult($scorm2004Sco, $user, $datas);
        }
        $scoTracking->setDetails($datas);
        $this->scormManager->updateScorm2004ScoTracking($scoTracking);

        return new Response('', '204');
    }

    /**
     * @EXT\Route(
     *     "scorm/12/{scorm}/configuration/edit/form",
     *     name="claro_scorm_12_configuration_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineScormBundle::scorm12ConfigurationModalForm.html.twig")
     *
     * @param Scorm12Resource $scorm
     */
    public function scorm12ConfigurationEditFormAction(Scorm12Resource $scorm)
    {
        $this->checkAccess('EDIT', $scorm);
        $form = $this->formFactory->create(new ScormConfigType(), $scorm);

        return ['form' => $form->createView(), 'scorm' => $scorm];
    }

    /**
     * @EXT\Route(
     *     "scorm/12/{scorm}/configuration/edit",
     *     name="claro_scorm_12_configuration_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineScormBundle::scorm12ConfigurationModalForm.html.twig")
     *
     * @param Scorm12Resource $scorm
     */
    public function scorm12ConfigurationEditAction(Scorm12Resource $scorm)
    {
        $this->checkAccess('EDIT', $scorm);
        $form = $this->formFactory->create(new ScormConfigType(), $scorm);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->scormManager->persistScorm12($scorm);

            return new JsonResponse('success', 200);
        } else {
            return ['form' => $form->createView(), 'scorm' => $scorm];
        }
    }

    /**
     * @EXT\Route(
     *     "scorm/2004/{scorm}/configuration/edit/form",
     *     name="claro_scorm_2004_configuration_edit_form",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineScormBundle::scorm2004ConfigurationModalForm.html.twig")
     *
     * @param Scorm12Resource $scorm
     */
    public function scorm2004ConfigurationEditFormAction(Scorm2004Resource $scorm)
    {
        $this->checkScorm2004ResourceAccess('EDIT', $scorm);
        $form = $this->formFactory->create(new ScormConfigType(), $scorm);

        return ['form' => $form->createView(), 'scorm' => $scorm];
    }

    /**
     * @EXT\Route(
     *     "scorm/2004/{scorm}/configuration/edit",
     *     name="claro_scorm_2004_configuration_edit",
     *     options={"expose"=true}
     * )
     * @EXT\Template("ClarolineScormBundle::scorm2004ConfigurationModalForm.html.twig")
     *
     * @param Scorm12Resource $scorm
     */
    public function scorm2004ConfigurationEditAction(Scorm2004Resource $scorm)
    {
        $this->checkScorm2004ResourceAccess('EDIT', $scorm);
        $form = $this->formFactory->create(new ScormConfigType(), $scorm);
        $form->handleRequest($this->request);

        if ($form->isValid()) {
            $this->scormManager->persistScorm2004($scorm);

            return new JsonResponse('success', 200);
        } else {
            return ['form' => $form->createView(), 'scorm' => $scorm];
        }
    }

    /**
     * Logs given datas as result of a Scorm resource.
     */
    private function logScorm2004ScoResult(Scorm2004Sco $sco, User $user, array $details)
    {
        $scormResource = $sco->getScormResource();

        if (isset($details['cmi.score.max']) &&
            isset($details['cmi.score.raw']) &&
            !empty($details['cmi.score.max']) &&
            !is_null($details['cmi.score.raw']) &&
            $details['cmi.score.max'] > 0) {
            $details['result'] = $details['cmi.score.raw'];
            $details['resultMax'] = $details['cmi.score.max'];
        }

        $event = new LogScorm2004ResultEvent($scormResource, $user, $details);
        $this->eventDispatcher->dispatch('log', $event);
    }

    /**
     * Converts time (HHHH:MM:SS.hh) to integer (hundredth of second).
     *
     * @param string $time
     */
    private function convertTimeInHundredth($time)
    {
        $timeInArray = explode(':', $time);
        $timeInArraySec = explode('.', $timeInArray[2]);
        $timeInHundredth = 0;

        if (isset($timeInArraySec[1])) {
            if (strlen($timeInArraySec[1]) === 1) {
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

    /**
     * Checks format of given session time interval and tries to fix format if invalid.
     *
     * @param string $sessionTime
     */
    private function formatSessionTime($sessionTime)
    {
        $formattedValue = 'PT0S';
        $generalPattern = '/^P([0-9]+Y)?([0-9]+M)?([0-9]+D)?T([0-9]+H)?([0-9]+M)?([0-9]+S)?$/';
        $decimalPattern = '/^P([0-9]+Y)?([0-9]+M)?([0-9]+D)?T([0-9]+H)?([0-9]+M)?[0-9]+\.[0-9]{1,2}S$/';

        if ($sessionTime !== 'PT') {
            if (preg_match($generalPattern, $sessionTime)) {
                $formattedValue = $sessionTime;
            } elseif (preg_match($decimalPattern, $sessionTime)) {
                $formattedValue = preg_replace(['/\.[0-9]+S$/'], ['S'], $sessionTime);
            }
        }

        return $formattedValue;
    }

    /**
     * Checks if the current user has the right to perform an action on a Scorm12Resource.
     *
     * @param string          $permission
     * @param Scorm12Resource $resource
     *
     * @throws AccessDeniedException
     */
    private function checkAccess($permission, Scorm12Resource $resource)
    {
        $collection = new ResourceCollection([$resource->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    /**
     * Checks if the current user has the right to perform an action on a Scorm2004Resource.
     *
     * @param string            $permission
     * @param Scorm2004Resource $resource
     *
     * @throws AccessDeniedException
     */
    private function checkScorm2004ResourceAccess($permission, Scorm2004Resource $resource)
    {
        $collection = new ResourceCollection([$resource->getResourceNode()]);

        if (!$this->authorization->isGranted($permission, $collection)) {
            throw new AccessDeniedException($collection->getErrorsForDisplay());
        }
    }

    private function hasScorm12Right(Scorm12Resource $scorm, $right)
    {
        $collection = new ResourceCollection([$scorm->getResourceNode()]);

        return $this->authorization->isGranted($right, $collection);
    }

    private function hasScorm2004Right(Scorm2004Resource $scorm, $right)
    {
        $collection = new ResourceCollection([$scorm->getResourceNode()]);

        return $this->authorization->isGranted($right, $collection);
    }
}
