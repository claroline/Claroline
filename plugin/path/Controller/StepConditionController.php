<?php

namespace Innova\PathBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Manager\GroupManager;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\TeamBundle\Manager\TeamManager;
use Innova\PathBundle\Entity\Path\Path;
use Innova\PathBundle\Entity\Step;
use Innova\PathBundle\Event\Log\LogStepUnlockDoneEvent;
use Innova\PathBundle\Event\Log\LogStepUnlockEvent;
use Innova\PathBundle\Manager\UserProgressionManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class StepConditionController.
 *
 * @Route(
 *     "/condition",
 *     options = {"expose" = true},
 *     service = "innova_path.controller.step_condition"
 * )
 */
class StepConditionController extends Controller
{
    /**
     * Object manager.
     *
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;
    private $groupManager;
    private $teamManager;
    private $eventDispatcher;
    /**
     * Security Token.
     *
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface
     */
    protected $securityToken;
    /**
     * User Progression manager.
     *
     * @var \Innova\PathBundle\Manager\UserProgressionManager
     */
    protected $userProgressionManager;

    /**
     * Constructor.
     *
     * @param ObjectManager            $objectManager
     * @param GroupManager             $groupManager
     * @param TokenStorageInterface    $securityToken
     * @param TeamManager              $teamManager
     * @param EventDispatcherInterface $eventDispatcher
     * @param UserProgressionManager   $userProgressionManager
     */
    public function __construct(
        ObjectManager $objectManager,
        GroupManager $groupManager,
        TokenStorageInterface $securityToken,
        TeamManager $teamManager,
        EventDispatcherInterface $eventDispatcher,
        UserProgressionManager $userProgressionManager
    ) {
        $this->groupManager = $groupManager;
        $this->om = $objectManager;
        $this->securityToken = $securityToken;
        $this->teamManager = $teamManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->userProgressionManager = $userProgressionManager;
    }

    /**
     * Get user group for criterion.
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/group",
     *     name = "innova_path_criteria_groups"
     * )
     * @Method("GET")
     */
    public function listGroupsAction()
    {
        $data = [];
        $groups = $this->groupManager->getAll();
        if ($groups) {
            // data needs to be explicitly set because Group does not extends Serializable
            /** @var \Claroline\CoreBundle\Entity\Group $group */
            foreach ($groups as $group) {
                $data[$group->getId()] = $group->getName();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Get list of groups a user belongs to.
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/group/current_user",
     *     name = "innova_path_criteria_user_groups"
     * )
     * @Method("GET")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function listUserGroupsAction(User $user = null)
    {
        $data = [];
        if ($user) {
            // Retrieve Groups of the User
            $groups = $user->getGroups();
            // data needs to be explicitly set because Group does not extends Serializable
            foreach ($groups as $group) {
                $data[$group->getId()] = $group->getName();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Get list of Evaluation statuses to display in select
     * (data from \CoreBundle\Entity\Activity\AbstractEvaluation.php).
     *
     * @Route(
     *     "/activity/statuses",
     *     name = "innova_path_criteria_activity_statuses",
     * )
     * @Method("GET")
     *
     * @return JsonResponse
     */
    public function listActivityStatusesAction()
    {
        $r = new \ReflectionClass('Claroline\CoreBundle\Entity\Activity\AbstractEvaluation');
        // Get class constants
        $const = $r->getConstants();
        $statuses = [];
        foreach ($const as $k => $v) {
            // Only get constants beginning with STATUS
            if (strpos($k, 'STATUS') !== false) {
                $statuses[] = $v;
            }
        }

        return new JsonResponse($statuses);
    }

    /**
     * Get evaluation data for an activity.
     *
     * @param Activity $activity
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/activity/evaluation/{id}",
     *     name         = "innova_path_criteria_evaluation",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getActivityEvaluation(Activity $activity)
    {
        $data = [
            'status' => 'NA',
            'attempts' => 0,
        ];

        // retrieve evaluation data for this activity
        $evaluationRepo = $this->om->getRepository('ClarolineCoreBundle:Activity\Evaluation');
        $evaluation = $evaluationRepo->findOneBy(['activityParameters' => $activity->getParameters()]);
        //return relevant data
        if (!empty($evaluation)) {
            $data = [
                'status' => $evaluation->getStatus(),
                'attempts' => $evaluation->getAttemptsCount(),
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * Get list of teams a user belongs to.
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/team/current_user",
     *     name = "innova_path_criteria_user_teams"
     * )
     * @Method("GET")
     * @ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     */
    public function listUserTeamsAction(User $user = null)
    {
        $data = [];
        if ($user) {
            // retrieve list of team object for this user
            $teams = $this->teamManager->getTeamsByUser($user, 'name', 'ASC', true);
            if ($teams) {
                // data needs to be explicitly set because Team does not extends Serializable
                /** @var \Claroline\TeamBundle\Entity\Team $team */
                foreach ($teams as $team) {
                    $data[$team->getId()] = $team->getName();
                }
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Get list of teams available in the Workspace of the current Path.
     *
     * @param Path $path
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/team/{id}",
     *     name = "innova_path_criteria_teams"
     * )
     * @Method("GET")
     */
    public function listTeamsAction(Path $path)
    {
        $data = [];
        // retrieve list of groups object for this user
        $teams = $this->teamManager->getTeamsByWorkspace($path->getWorkspace());
        if ($teams) {
            // data needs to be explicitly set because Team does not extends Serializable
            /** @var \Claroline\TeamBundle\Entity\Team $team */
            foreach ($teams as $team) {
                $data[$team->getId()] = $team->getName();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @param Step $step
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @Route(
     *     "/stepunlock/{step}",
     *     name         = "innova_path_step_callforunlock",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function callForUnlock(Step $step)
    {
        //array of user id to send the notification to = users who will receive the call : the path creator
        $creator = $step->getPath()->getCreator()->getId();
        $userIds = [$creator];
        //create an event, and pass parameters
        $event = new LogStepUnlockEvent($step, $userIds);
        //send the event to the event dispatcher
        $this->eventDispatcher->dispatch('log', $event);

        //update lockedcall value : set to true = called
        $user = $this->securityToken->getToken()->getUser();
        $progression = $this->userProgressionManager
            ->updateLockedState($user, $step, true, null, null, '');
        //return response
        return new JsonResponse($progression);
    }

    /**
     * Ajax call for unlocking step.
     *
     * @Route(
     *     "unlockstep/{step}/user/{user}",
     *     name="innova_path_unlock_step",
     *     options={"expose"=true}
     * )
     * @Method("GET")
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function unlockStep(Step $step, User $user)
    {
        $userIds = [$user->getId()];
        //create an event, and pass parameters
        $event = new LogStepUnlockDoneEvent($step, $userIds);
        //send the event to the event dispatcher
        $this->eventDispatcher->dispatch('log', $event);
        //update lockedcall value : set to true = called
        $progression = $this->userProgressionManager
            ->updateLockedState($user, $step, false, false, true, 'unseen');
        //return response
        return new JsonResponse($progression);
    }
}
