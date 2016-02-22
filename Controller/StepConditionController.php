<?php

namespace Innova\PathBundle\Controller;

use Claroline\TeamBundle\Manager\TeamManager;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Manager\GroupManager;
use Innova\PathBundle\Manager\StepConditionsGroupManager;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Claroline\CoreBundle\Entity\Activity\AbstractEvaluation;

/**
 * Class StepConditionController
 *
 * @Route(
 *      "/stepconditions",
 *      name    = "innova_path_stepcondition",
 *      service = "innova_path.controller.step_condition"
 * )
 */
class StepConditionController extends Controller
{
    /**
     * Object manager
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    private $om;
    private $groupManager;
    private $evaluationRepo;
    private $teamManager;
    /**
     * Security Token
     * @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $securityToken
     */
    protected $securityToken;

    /**
     * Constructor
     *
     * @param ObjectManager $objectManager
     * @param GroupManager $groupManager
     * @param TokenStorageInterface $securityToken
     * @param TeamManager $teamManager
     */
    public function __construct(
        ObjectManager $objectManager,
        GroupManager $groupManager,
        TokenStorageInterface $securityToken,
        TeamManager $teamManager
    )
    {
        $this->groupManager  = $groupManager;
        $this->om            = $objectManager;
        $this->securityToken = $securityToken;
        $this->teamManager   = $teamManager;
    }
    /**
     * Get user group for criterion
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/usergroup",
     *     name         = "innova_path_criteria_usergroup",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getUserGroups()
    {
        $data = array();

        $usergroup = $this->groupManager->getAllGroupsWithoutPager();
        if ($usergroup != null) {
            //data needs to be explicitly set because Group does not extends Serializable
            foreach($usergroup as $ug) {
                $data[$ug->getId()] = $ug->getName();
            }
        }

        return new JsonResponse($data);
    }

    /**
     * Get list of groups a user belongs to
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/groupsforuser",
     *     name         = "innova_path_criteria_groupsforuser",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getGroupsForUser()
    {
        // Retrieve the current User
        $user = $this->securityToken->getToken()->getUser();
        // Retrieve Groups of the User
        $groups = $user->getGroups();

        // data needs to be explicitly set because Group does not extends Serializable
        $data = array();
        foreach($groups as $group) {
            $data[$group->getId()] = $group->getName();
        }

        return new JsonResponse($data);
    }

    /**
     * Get evaluation data for an activity
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/activityeval/{activityId}",
     *     name         = "innova_path_activity_eval",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getActivityEvaluation($activityId)
    {
        $data = array(
            'status' => 'NA',
            'attempts' => 0
        );
        //retrieve activity
        $this->activityRepo = $this->om->getRepository('ClarolineCoreBundle:Resource\Activity');
        $activity = $this->activityRepo->findOneBy(array('id'=>$activityId));
        if ($activity !== null)
        {
            //retrieve evaluation data for this activity
            $this->evaluationRepo = $this->om->getRepository('ClarolineCoreBundle:Activity\Evaluation');
            $evaluation = $this->evaluationRepo->findOneBy(array('activityParameters'=> $activity->getParameters()));
            //return relevant data
            if ($evaluation !== null){
                $data = array(
                    'status' => $evaluation->getStatus(),
                    'attempts' => $evaluation->getAttemptsCount()
                );
            }
        }
        return new JsonResponse($data);
    }

    /**
     * Get list of Evaluation statuses to display in select
     * (data from \CoreBundle\Entity\Activity\AbstractEvaluation.php)
     * @Route(
     *     "/activitystatuses",
     *     name         = "innova_path_criteria_activitystatuses",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getEvaluationStatuses()
    {
/*
        $statuses = array(
            AbstractEvaluation::STATUS_COMPLETED,
            AbstractEvaluation::STATUS_FAILED,
            AbstractEvaluation::STATUS_INCOMPLETE,
            AbstractEvaluation::STATUS_NOT_ATTEMPTED,
            AbstractEvaluation::STATUS_PASSED,
            AbstractEvaluation::STATUS_UNKNOWN
        );
*/

        $r = new \ReflectionClass('Claroline\CoreBundle\Entity\Activity\AbstractEvaluation');
        //Get class constants
        $const = $r->getConstants();
        $statuses = array();
        foreach($const as $k => $v) {
            //Only get constants beginning with STATUS
            if (strpos($k, 'STATUS') !== false)
                $statuses[] = $v;
        }

        return new JsonResponse($statuses);
    }
    /**
     * Get activities of steps of a path
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/activitylist/{path}",
     *     name         = "innova_path_activities",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getActivityList(Path $path){
        $activitylist = array();
        $steps = $this->om->getRepository('InnovaPathBundle:Path')->findById($path);

        foreach($steps as $step){
            $activitylist[$step->getId()] = StepConditionController::getActivityEvaluation($step->getActivity());
        }
        return new JsonResponse($activitylist);
    }

    /**
     * Get evaluation for all steps of a path
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/allevaluations/{path}",
     *     name         = "innova_path_evaluation",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getAllEvaluationsByUserByPath($path)
    {
        $user = $this->securityToken->getToken()->getUser();
        $results = $this->om->getRepository('InnovaPathBundle:StepCondition')->findAllEvaluationsByUserAndByPath((int)$path, $user->getId());

        $jsonresults = array();
        foreach($results as $r)
        {
            $jsonresults[] = array(
                'eval' => array(
                    'id' => $r->getId(),
                    'attempts' => $r->getAttemptsCount(),
                    'status' => $r->getStatus(),
                    'score' => $r->getScore(),
                    'numscore' => $r->getNumScore(),
                    'scooremin' => $r->getScoreMin(),
                    'scoremax' => $r->getScoreMax(),
                    'type' => $r->getType(),
                ),
                'evaltype'    => $r->getActivityParameters()->getEvaluationType(),
                'idactivity'    => $r->getActivityParameters()->getActivity()->getId(),
                'activitytitle'    => $r->getActivityParameters()->getActivity()->getTitle(),
            );
        }

        return new JsonResponse($jsonresults);
    }

    /**
     * Get list of teams for current WS
     * @param $id path_id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/teamsforws/{id}",
     *     name         = "innova_path_criteria_teamsforws",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getTeamsForWs($id)
    {
        //retrieve current workspace
        $workspace = $this->om->getRepository("InnovaPathBundle:Path\Path")->findOneById($id)->getWorkspace();
        //$workspace = $this->om->getRepository("ClarolineCoreBundle:Resource\ResourceNode")->findOneById($id)->getWorkspace();
        $data = array();
        //retrieve list of groups object for this user
        $teamsforws = $this->teamManager->getTeamsByWorkspace($workspace);
        if ($teamsforws != null) {
            //data needs to be explicitly set because Team does not extends Serializable
            foreach($teamsforws as $tw) {
                $data[$tw->getId()] = $tw->getName();
            }
        }
        return new JsonResponse($data);
    }

    /**
     * Get list of teams a user belongs to
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @Route(
     *     "/teamsforuser",
     *     name         = "innova_path_criteria_teamsforuser",
     *     options      = { "expose" = true }
     * )
     * @Method("GET")
     */
    public function getTeamsForUser()
    {
        //retrieve current user
        $user = $this->securityToken->getToken()->getUser();
        $userId = $user->getId();
        $data = array();
        //retrieve list of team object for this user
        $teamforuser = $this->teamManager->getTeamsByUser($user, 'name', 'ASC', true);
        if ($teamforuser != null) {
            //data needs to be explicitly set because Team does not extends Serializable
            foreach($teamforuser as $tu) {
                $data[$tu->getId()] = $tu->getName();
            }
        }
        return new JsonResponse($data);
    }
}