<?php

namespace Innova\PathBundle\Controller\Api;

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
use JMS\DiExtraBundle\Annotation as DI;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @EXT\Route("/condition", options={"expose"=true})
 */
class StepConditionController extends Controller
{
    /**
     * @var ObjectManager
     */
    private $om;

    /**
     * @var GroupManager
     */
    private $groupManager;

    /**
     * @var TeamManager
     */
    private $teamManager;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * StepConditionController constructor.
     *
     * @DI\InjectParams({
     *     "objectManager"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "tokenStorage"           = @DI\Inject("security.token_storage"),
     *     "eventDispatcher"        = @DI\Inject("event_dispatcher"),
     *     "groupManager"           = @DI\Inject("claroline.manager.group_manager"),
     *     "teamManager"            = @DI\Inject("claroline.manager.team_manager"),
     *     "userProgressionManager" = @DI\Inject("innova_path.manager.user_progression")
     * })
     *
     * @param ObjectManager            $objectManager
     * @param TokenStorageInterface    $tokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     * @param GroupManager             $groupManager
     * @param TeamManager              $teamManager
     * @param UserProgressionManager   $userProgressionManager
     */
    public function __construct(
        ObjectManager $objectManager,
        TokenStorageInterface $tokenStorage,
        EventDispatcherInterface $eventDispatcher,
        GroupManager $groupManager,
        TeamManager $teamManager,
        UserProgressionManager $userProgressionManager
    ) {
        $this->om = $objectManager;
        $this->tokenStorage = $tokenStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->groupManager = $groupManager;
        $this->teamManager = $teamManager;
        $this->userProgressionManager = $userProgressionManager;
    }

    /**
     * @var UserProgressionManager
     */
    protected $userProgressionManager;

    /**
     * Get user group for criterion.
     *
     * @EXT\Route("/group", name="innova_path_criteria_groups")
     * @EXT\Method("GET")
     *
     * @return JsonResponse
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
     * @EXT\Route("/group/current_user", name="innova_path_criteria_user_groups")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User $user
     *
     * @return JsonResponse
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
     * @EXT\Route("/activity/statuses", name="innova_path_criteria_activity_statuses")
     * @EXT\Method("GET")
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
     * @EXT\Route("/activity/evaluation/{id}", name="innova_path_criteria_evaluation")
     * @EXT\Method("GET")
     *
     * @param Activity $activity
     *
     * @return JsonResponse
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
     * @EXT\Route("/team/current_user", name="innova_path_criteria_user_teams")
     * @EXT\Method("GET")
     * @EXT\ParamConverter("user", converter="current_user", options={"allowAnonymous"=true})
     *
     * @param User $user
     *
     * @return JsonResponse
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
     * @EXT\Route("/team/{id}", name="innova_path_criteria_teams")
     * @EXT\Method("GET")
     *
     * @param Path $path
     *
     * @return JsonResponse
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
     * @EXT\Route("/stepunlock/{step}", name="innova_path_step_callforunlock")
     * @EXT\Method("GET")
     *
     * @param Step $step
     *
     * @return JsonResponse
     */
    public function callForUnlockAction(Step $step)
    {
        //array of user id to send the notification to = users who will receive the call : the path creator
        $creator = $step->getPath()->getCreator()->getId();
        $userIds = [$creator];
        //create an event, and pass parameters
        $event = new LogStepUnlockEvent($step, $userIds);
        //send the event to the event dispatcher
        $this->eventDispatcher->dispatch('log', $event);

        // update locked call value : set to true = called
        $user = $this->tokenStorage->getToken()->getUser();
        $progression = $this->userProgressionManager
            ->updateLockedState($user, $step, true, null, null, '');
        //return response
        return new JsonResponse($progression);
    }

    /**
     * Ajax call for unlocking step.
     *
     * @EXT\Route("unlockstep/{step}/user/{user}", name="innova_path_unlock_step")
     * @EXT\Method("GET")
     *
     * @param Step $step
     * @param User $user
     *
     * @return JsonResponse
     */
    public function unlockStepAction(Step $step, User $user)
    {
        $userIds = [$user->getId()];
        //create an event, and pass parameters
        $event = new LogStepUnlockDoneEvent($step, $userIds);
        //send the event to the event dispatcher
        $this->eventDispatcher->dispatch('log', $event);
        //update locked call value : set to true = called
        $progression = $this->userProgressionManager
            ->updateLockedState($user, $step, false, false, true, 'unseen');
        //return response
        return new JsonResponse($progression);
    }
}
