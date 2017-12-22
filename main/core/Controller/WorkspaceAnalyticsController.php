<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller;

use Claroline\CoreBundle\Entity\Activity\AbstractEvaluation;
use Claroline\CoreBundle\Entity\Activity\ActivityParameters;
use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Security\Utilities;
use Claroline\CoreBundle\Manager\ActivityManager;
use Claroline\CoreBundle\Manager\AnalyticsManager;
use Claroline\CoreBundle\Manager\ResourceManager;
use Claroline\CoreBundle\Manager\RoleManager;
use Claroline\CoreBundle\Manager\UserManager;
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\TwigBundle\TwigEngine;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WorkspaceAnalyticsController extends Controller
{
    private $activityManager;
    private $analyticsManager;
    private $resourceManager;
    private $roleManager;
    private $tokenStorage;
    private $authorization;
    private $templating;
    private $userManager;
    private $utils;

    /**
     * @DI\InjectParams({
     *     "activityManager"  = @DI\Inject("claroline.manager.activity_manager"),
     *     "analyticsManager" = @DI\Inject("claroline.manager.analytics_manager"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "authorization"   = @DI\Inject("security.authorization_checker"),
     *     "tokenStorage"    = @DI\Inject("security.token_storage"),
     *     "templating"       = @DI\Inject("templating"),
     *     "userManager"      = @DI\Inject("claroline.manager.user_manager"),
     *     "utils"            = @DI\Inject("claroline.security.utilities")
     * })
     */
    public function __construct(
        ActivityManager $activityManager,
        AnalyticsManager $analyticsManager,
        ResourceManager $resourceManager,
        RoleManager $roleManager,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorization,
        TwigEngine $templating,
        UserManager $userManager,
        Utilities $utils
    ) {
        $this->activityManager = $activityManager;
        $this->analyticsManager = $analyticsManager;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorization = $authorization;
        $this->templating = $templating;
        $this->userManager = $userManager;
        $this->utils = $utils;
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/traffic",
     *     name="claro_workspace_analytics_traffic"
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool/workspace/analytics:traffic.html.twig")
     *
     * Displays activities evaluations home tab of analytics tool
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return Response
     */
    public function showTrafficAction(Workspace $workspace)
    {
        if (!$this->authorization->isGranted('analytics', $workspace)) {
            throw new AccessDeniedException();
        }

        $chartData = $this->analyticsManager->getDailyActionNumberForDateRange(
            $this->analyticsManager->getDefaultRange(),
            'workspace-enter',
            false,
            [$workspace->getId()]
        );

        return [
            'analyticsTab' => 'traffic',
            'workspace' => $workspace,
            'chartData' => $chartData,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/resources",
     *     name="claro_workspace_analytics_resources"
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool/workspace/analytics:resources.html.twig")
     *
     * Displays workspace analytics resource page.
     *
     * @param \Claroline\CoreBundle\Entity\Workspace\Workspace $workspace
     *
     * @return Response
     */
    public function showResourcesAction(Workspace $workspace)
    {
        $typeCount = $this->analyticsManager->getWorkspaceResourceTypesCount($workspace);

        return [
            'analyticsTab' => 'resources',
            'workspace' => $workspace,
            'resourceCount' => $typeCount,
        ];
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/activities/evaluations",
     *     name="claro_workspace_activities_evaluations_show"
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     *
     * Displays activities evaluations home tab of analytics tool
     *
     * @param User      $currentUser
     * @param Workspace $workspace
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function workspaceActivitiesEvaluationsShowAction(
        User $currentUser,
        Workspace $workspace
    ) {
        if (!$this->authorization->isGranted('analytics', $workspace)) {
            throw new AccessDeniedException();
        }

        $roleNames = $currentUser->getRoles();
        $isWorkspaceManager = $this->isWorkspaceManager($workspace, $roleNames);

        if ($isWorkspaceManager) {
            $activities = $this->activityManager
                ->getActivityByWorkspace($workspace);

            // It only allows to prevent 1 DB request per activity when getting
            // resourceNode linked to activity
            $resourceType = $this->resourceManager->getResourceTypeByName('activity');
            $this->resourceManager
                ->getByWorkspaceAndResourceType($workspace, $resourceType);

            return new Response(
                $this->templating->render(
                    'ClarolineCoreBundle:Tool/workspace/analytics:workspaceManagerActivitiesEvaluations.html.twig',
                    [
                        'analyticsTab' => 'activities',
                        'workspace' => $workspace,
                        'activities' => $activities,
                    ]
                )
            );
        } else {
            $token = $this->tokenStorage->getToken();
            $userRoles = $this->utils->getRoles($token);

            $criteria = [];
            $criteria['roots'] = [];

            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $criteria['roots'][] = $root->getPath();

            $criteria['types'] = ['activity'];
            $nodes = $this->resourceManager
                ->getByCriteria($criteria, $userRoles);
            $resourceNodeIds = [];

            foreach ($nodes as $node) {
                $resourceNodeIds[] = $node['id'];
            }
            $activities = $this->activityManager
                ->getActivitiesByResourceNodeIds($resourceNodeIds);

            $params = [];

            foreach ($activities as $activity) {
                $params[] = $activity->getParameters();
            }

            $evaluations =
                $this->activityManager->getEvaluationsByUserAndActivityParameters(
                    $currentUser,
                    $params
                );

            $evaluationsAssoc = [];

            foreach ($evaluations as $evaluation) {
                $activityId = $evaluation->getActivityParameters()->getActivity()->getId();
                $evaluationsAssoc[$activityId] = $evaluation;
            }

            $rulesScores = [];
            $nbSuccess = 0;

            foreach ($activities as $activity) {
                $params = $activity->getParameters();
                $evaluationType = $params->getEvaluationType();

                if (!isset($evaluationsAssoc[$activity->getId()])) {
                    $evaluationsAssoc[$activity->getId()] = $this->activityManager
                        ->createBlankEvaluation($currentUser, $params);
                }

                if ($evaluationType === AbstractEvaluation::TYPE_AUTOMATIC
                    && count($params->getRules()) > 0) {
                    $rule = $params->getRules()->first();
                    $isResultVisible = $rule->getIsResultVisible();

                    if (!empty($isResultVisible)) {
                        $score = $rule->getResult();
                        $scoreMax = $rule->getResultMax();

                        if (!is_null($score)) {
                            $ruleScore = $score;

                            if (!is_null($scoreMax)) {
                                $ruleScore .= ' / '.$scoreMax;
                            }

                            $rulesScores[$activity->getId()] = $ruleScore;
                        }
                    }
                }

                $status = $evaluationsAssoc[$activity->getId()]->getStatus();

                if ($status === AbstractEvaluation::STATUS_COMPLETED
                    || $status === AbstractEvaluation::STATUS_PASSED) {
                    ++$nbSuccess;
                }
            }

            $progress = count($activities) > 0 ?
                round($nbSuccess / count($activities), 2) * 100 :
                0;

            return new Response(
                $this->templating->render(
                    'ClarolineCoreBundle:Tool/workspace/analytics:workspaceActivitiesEvaluations.html.twig',
                    [
                        'analyticsTab' => 'activities',
                        'workspace' => $workspace,
                        'activities' => $activities,
                        'evaluations' => $evaluationsAssoc,
                        'rulesScores' => $rulesScores,
                        'progress' => $progress,
                    ]
                )
            );
        }
    }

    /**
     * @EXT\Route(
     *     "/{workspaceId}/activity/parameters/{activityParametersId}/user/{userId}/past/evaluations/show/{displayType}",
     *     name="claro_workspace_activities_past_evaluations_show",
     *     options = {"expose": true}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "user",
     *      class="ClarolineCoreBundle:User",
     *      options={"id" = "userId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "workspace",
     *      class="ClarolineCoreBundle:Workspace\Workspace",
     *      options={"id" = "workspaceId", "strictId" = true}
     * )
     * @EXT\ParamConverter(
     *      "activityParameters",
     *      class="ClarolineCoreBundle:Activity\ActivityParameters",
     *      options={"id" = "activityParametersId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool/workspace/analytics:workspaceActivitiesPastEvaluations.html.twig")
     *
     * Displays past evaluations of one activity for one user
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function workspaceActivitiesPastEvaluationsShowAction(
        User $currentUser,
        User $user,
        Workspace $workspace,
        ActivityParameters $activityParameters,
        $displayType
    ) {
        if (!$this->authorization->isGranted('analytics', $workspace)) {
            throw new AccessDeniedException();
        }
        $roleNames = $currentUser->getRoles();
        $isWorkspaceManager = $this->isWorkspaceManager($workspace, $roleNames);

        if (!$isWorkspaceManager && ($currentUser->getId() !== $user->getId())) {
            throw new AccessDeniedException();
        }
        $activity = $activityParameters->getActivity();
        $ruleScore = null;
        $isResultVisible = false;

        if ($activityParameters->getEvaluationType() === AbstractEvaluation::TYPE_AUTOMATIC
            && count($activityParameters->getRules()) > 0) {
            $rule = $activityParameters->getRules()->first();
            $score = $rule->getResult();
            $scoreMax = $rule->getResultMax();

            if (!is_null($score)) {
                $ruleScore = $score;

                if (!is_null($scoreMax)) {
                    $ruleScore .= ' / '.$scoreMax;
                }

                $ruleResultVisible = $rule->getIsResultVisible();
                $isResultVisible = !empty($ruleResultVisible);
            }
        }

        $pastEvals =
            $this->activityManager->getPastEvaluationsByUserAndActivityParams(
                $user,
                $activityParameters
            );

        return [
            'user' => $user,
            'activity' => $activity,
            'pastEvals' => $pastEvals,
            'displayType' => $displayType,
            'isWorkspaceManager' => $isWorkspaceManager,
            'ruleScore' => $ruleScore,
            'isResultVisible' => $isResultVisible,
        ];
    }

    /**
     * @EXT\Route(
     *     "/workspace/manager/activity/{activityId}/evaluations",
     *     name="claro_workspace_manager_activity_evaluations_show",
     *     defaults={"page"=1}
     * )
     * @EXT\ParamConverter("currentUser", options={"authenticatedUser" = true})
     * @EXT\ParamConverter(
     *      "activity",
     *      class="ClarolineCoreBundle:Resource\Activity",
     *      options={"id" = "activityId", "strictId" = true}
     * )
     * @EXT\Template("ClarolineCoreBundle:Tool/workspace/analytics:workspaceManagerActivityEvaluations.html.twig")
     *
     * Displays evaluations of an activity for each user of the workspace
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function workspaceManagerActivityEvaluationsShowAction(
        User $currentUser,
        Activity $activity
    ) {
        $roleNames = $currentUser->getRoles();
        $workspace = $activity->getResourceNode()->getWorkspace();
        $isWorkspaceManager = $this->isWorkspaceManager($workspace, $roleNames);

        if (!$isWorkspaceManager) {
            throw new AccessDeniedException();
        }

        $resourceNode = $activity->getResourceNode();
        $activityParams = $activity->getParameters();
        $roles = $this->roleManager
            ->getRolesWithRightsByResourceNode($resourceNode);
        $usersPager = $this->userManager->getUsersByRolesIncludingGroups($roles);
        $users = [];

        foreach ($usersPager as $user) {
            $users[] = $user;
        }

        $allEvaluations = $this->activityManager
            ->getEvaluationsByUsersAndActivityParams($users, $activityParams);
        $evaluations = [];

        foreach ($allEvaluations as $evaluation) {
            $user = $evaluation->getUser();
            $evaluations[$user->getId()] = $evaluation;
        }

        $nbSuccess = 0;

        foreach ($users as $user) {
            if (!isset($evaluations[$user->getId()])) {
                $evaluations[$user->getId()] = $this->activityManager
                    ->createBlankEvaluation($user, $activityParams);
            }

            $status = $evaluations[$user->getId()]->getStatus();

            if ($status === AbstractEvaluation::STATUS_COMPLETED
                || $status === AbstractEvaluation::STATUS_PASSED) {
                ++$nbSuccess;
            }
        }
        $progress = count($users) > 0 ?
            round($nbSuccess / count($users), 2) * 100 :
            0;

        $ruleScore = null;

        if ($activityParams->getEvaluationType() === AbstractEvaluation::TYPE_AUTOMATIC
            && count($activityParams->getRules()) > 0) {
            $rule = $activityParams->getRules()->first();
            $score = $rule->getResult();
            $scoreMax = $rule->getResultMax();

            if (!is_null($score)) {
                $ruleScore = $score;

                if (!is_null($scoreMax)) {
                    $ruleScore .= ' / '.$scoreMax;
                }
            }
        }

        return [
            'analyticsTab' => 'activities',
            'activity' => $activity,
            'activityParams' => $activityParams,
            'workspace' => $workspace,
            'users' => $usersPager,
            'evaluations' => $evaluations,
            'ruleScore' => $ruleScore,
            'progress' => $progress,
        ];
    }

    private function isWorkspaceManager(Workspace $workspace, array $roleNames)
    {
        $isWorkspaceManager = false;
        $managerRole = 'ROLE_WS_MANAGER_'.$workspace->getGuid();

        if (in_array('ROLE_ADMIN', $roleNames) ||
            in_array($managerRole, $roleNames)) {
            $isWorkspaceManager = true;
        }

        return $isWorkspaceManager;
    }
}
