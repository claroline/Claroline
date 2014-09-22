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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;

class WorkspaceAnalyticsController extends Controller
{
    private $activityManager;
    private $analyticsManager;
    private $resourceManager;
    private $roleManager;
    private $securityContext;
    private $templating;
    private $userManager;
    private $utils;

    /**
     * @DI\InjectParams({
     *     "activityManager"  = @DI\Inject("claroline.manager.activity_manager"),
     *     "analyticsManager" = @DI\Inject("claroline.manager.analytics_manager"),
     *     "resourceManager"  = @DI\Inject("claroline.manager.resource_manager"),
     *     "roleManager"      = @DI\Inject("claroline.manager.role_manager"),
     *     "securityContext"  = @DI\Inject("security.context"),
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
        SecurityContextInterface $securityContext,
        TwigEngine $templating,
        UserManager $userManager,
        Utilities $utils
    )
    {
        $this->activityManager = $activityManager;
        $this->analyticsManager = $analyticsManager;
        $this->resourceManager = $resourceManager;
        $this->roleManager = $roleManager;
        $this->securityContext = $securityContext;
        $this->templating = $templating;
        $this->userManager = $userManager;
        $this->utils = $utils;
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
     * @return Response
     */
    public function showResourcesAction(Workspace $workspace)
    {
        $typeCount = $this->analyticsManager->getWorkspaceResourceTypesCount($workspace);

        return array(
            'analyticsTab' => 'resources',
            'workspace' => $workspace,
            'resourceCount' => $typeCount
        );
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
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @return Response
     */
    public function showTrafficAction(Workspace $workspace)
    {
        if (!$this->securityContext->isGranted('analytics', $workspace)) {
            throw new AccessDeniedException();
        }

        $chartData = $this->analyticsManager->getDailyActionNumberForDateRange(
            $this->analyticsManager->getDefaultRange(),
            'workspace-enter',
            false,
            array($workspace->getId())
        );

        return array(
            'analyticsTab' => 'traffic',
            'workspace' => $workspace,
            'chartData' => $chartData
        );
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
     * @return Response
     *
     * @throws \Exception
     */
    public function workspaceActivitiesEvaluationsShowAction(
        User $currentUser,
        Workspace $workspace
    )
    {
        if (!$this->securityContext->isGranted('analytics', $workspace)) {

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
            $resourceNodes = $this->resourceManager
                ->getByWorkspaceAndResourceType($workspace, $resourceType);

            return new Response(
                $this->templating->render(
                    "ClarolineCoreBundle:Tool/workspace/analytics:workspaceManagerActivitiesEvaluations.html.twig",
                    array(
                        'analyticsTab' => 'activities',
                        'workspace' => $workspace,
                        'activities' => $activities
                    )
                )
            );
        } else {
            $token = $this->securityContext->getToken();
            $userRoles = $this->utils->getRoles($token);

            $criteria = array();
            $criteria['roots'] = array();

            $root = $this->resourceManager->getWorkspaceRoot($workspace);
            $criteria['roots'][] = $root->getPath();

            $criteria['types'] = array('activity');
            $nodes = $this->resourceManager
                ->getByCriteria($criteria, $userRoles, true);
            $resourceNodeIds = array();

            foreach ($nodes as $node) {
                $resourceNodeIds[] = $node['id'];
            }
            $activities = $this->activityManager
                ->getActivitiesByResourceNodeIds($resourceNodeIds);

            $params = array();

            foreach ($activities as $activity) {
                $params[] = $activity->getParameters();
            }

            $evaluations =
                $this->activityManager->getEvaluationsByUserAndActivityParameters(
                    $currentUser,
                    $params
                );

            $evaluationsAssoc = array();

            foreach ($evaluations as $evaluation) {
                $activityId = $evaluation->getActivityParameters()->getActivity()->getId();
                $evaluationsAssoc[$activityId] = $evaluation;
            }

            $rulesScores = array();
            $nbSuccess = 0;

            foreach ($activities as $activity) {
                $params = $activity->getParameters();
                $evaluationType = $params->getEvaluationType();

                if (!isset($evaluationsAssoc[$activity->getId()])) {
                    $status = ($evaluationType === 'automatic') ?
                        'not_attempted' :
                        null;

                    $evaluation = $this->activityManager->createEvaluation(
                        $currentUser,
                        $params,
                        null,
                        null,
                        $status
                    );
                    $evaluationsAssoc[$activity->getId()] = $evaluation;
                }

                if ($evaluationType === 'automatic' &&
                    count($params->getRules()) > 0) {

                    $rule = $params->getRules()->first();
                    $isResultVisible = $rule->getIsResultVisible();

                    if (!empty($isResultVisible)) {
                        $score = $rule->getResult();
                        $scoreMax = $rule->getResultMax();

                        if (!is_null($score)) {
                            $ruleScore = $score;

                            if (!is_null($scoreMax)) {
                                $ruleScore .= ' / ' . $scoreMax;
                            }

                            $rulesScores[$activity->getId()] = $ruleScore;
                        }
                    }
                }

                if ($evaluationsAssoc[$activity->getId()]->getStatus() === 'completed' ||
                    $evaluationsAssoc[$activity->getId()]->getStatus() === 'passed') {

                    $nbSuccess++;
                }
            }

            $progress = count($activities) > 0 ?
                round($nbSuccess / count($activities), 2) * 100 :
                0;

            return new Response(
                $this->templating->render(
                    "ClarolineCoreBundle:Tool/workspace/analytics:workspaceActivitiesEvaluations.html.twig",
                    array(
                        'analyticsTab' => 'activities',
                        'workspace' => $workspace,
                        'activities' => $activities,
                        'evaluations' => $evaluationsAssoc,
                        'rulesScores' => $rulesScores,
                        'progress' => $progress
                    )
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
    )
    {
        if (!$this->securityContext->isGranted('analytics', $workspace)) {

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

        if ($activityParameters->getEvaluationType() === 'automatic' &&
            count($activityParameters->getRules()) > 0) {

            $rule = $activityParameters->getRules()->first();
            $score = $rule->getResult();
            $scoreMax = $rule->getResultMax();

            if (!is_null($score)) {
                $ruleScore = $score;

                if (!is_null($scoreMax)) {
                    $ruleScore .= ' / ' . $scoreMax;
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

        return array(
            'user' => $user,
            'activity' => $activity,
            'pastEvals' => $pastEvals,
            'displayType' => $displayType,
            'isWorkspaceManager' => $isWorkspaceManager,
            'ruleScore' => $ruleScore,
            'isResultVisible' => $isResultVisible
        );
    }

    /**
     * @EXT\Route(
     *     "/workspace/manager/activity/{activityId}/evaluations/page/{page}",
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
        Activity $activity,
        $page
    )
    {
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
        $usersPager = $this->userManager
            ->getUsersByRolesIncludingGroups($roles, $page);
        $users = array();

        foreach ($usersPager as $user) {
            $users[] = $user;
        }
        $allEvaluations = $this->activityManager
            ->getEvaluationsByUsersAndActivityParams($users, $activityParams);
        $evaluations = array();

        foreach ($allEvaluations as $evaluation) {
            $user = $evaluation->getUser();
            $evaluations[$user->getId()] = $evaluation;
        }

        $nbSuccess = 0;

        foreach ($users as $user) {

            if (!isset($evaluations[$user->getId()])) {
                $evaluationType = $activityParams->getEvaluationType();
                $status = ($evaluationType === 'automatic') ?
                    'not_attempted' :
                    null;

                $evaluation = $this->activityManager->createEvaluation(
                    $user,
                    $activityParams,
                    null,
                    null,
                    $status
                );
                $evaluations[$user->getId()] = $evaluation;
            }

            if ($evaluations[$user->getId()]->getStatus() === 'completed' ||
                $evaluations[$user->getId()]->getStatus() === 'passed') {

                $nbSuccess++;
            }
        }
        $progress = count($users) > 0 ?
            round($nbSuccess / count($users), 2) * 100 :
            0;

        $ruleScore = null;

        if ($activityParams->getEvaluationType() === 'automatic' &&
            count($activityParams->getRules()) > 0) {

            $rule = $activityParams->getRules()->first();
            $score = $rule->getResult();
            $scoreMax = $rule->getResultMax();

            if (!is_null($score)) {
                $ruleScore = $score;

                if (!is_null($scoreMax)) {
                    $ruleScore .= ' / ' . $scoreMax;
                }
            }
        }

        return array(
            'analyticsTab' => 'activities',
            'activity' => $activity,
            'activityParams' => $activityParams,
            'workspace' => $workspace,
            'users' => $usersPager,
            'page' => $page,
            'evaluations' => $evaluations,
            'ruleScore' => $ruleScore,
            'progress' => $progress
        );
    }

    private function isWorkspaceManager(Workspace $workspace, array $roleNames)
    {
        $isWorkspaceManager = false;
        $managerRole = 'ROLE_WS_MANAGER_' . $workspace->getGuid();

        if (in_array('ROLE_ADMIN', $roleNames) ||
            in_array($managerRole, $roleNames)) {

            $isWorkspaceManager = true;
        }

        return $isWorkspaceManager;
    }
}
