<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\Group;
use Claroline\CoreBundle\Entity\User;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Entity\ObjectiveCompetency;
use HeVinci\CompetencyBundle\Form\Handler\FormHandler;
use HeVinci\CompetencyBundle\Manager\ObjectiveManager;
use HeVinci\CompetencyBundle\Manager\ProgressManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('learning-objectives')")
 * @EXT\Route(
 *     "/objectives",
 *     requirements={"id"="\d+", "userId"="\d+", "groupId"="\d+"},
 *     options={"expose"=true}
 * )
 * @EXT\Method("GET")
 */
class ObjectiveController
{
    private $formHandler;
    private $objectiveManager;
    private $progressManager;

    /**
     * @DI\InjectParams({
     *     "handler"            = @DI\Inject("hevinci.form.handler"),
     *     "objectiveManager"   = @DI\Inject("hevinci.competency.objective_manager"),
     *     "progressManager"    = @DI\Inject("hevinci.competency.progress_manager"),
     * })
     *
     * @param FormHandler       $handler
     * @param ObjectiveManager  $objectiveManager
     * @param ProgressManager   $progressManager
     */
    public function __construct(
        FormHandler $handler,
        ObjectiveManager $objectiveManager,
        ProgressManager $progressManager
    )
    {
        $this->formHandler = $handler;
        $this->objectiveManager = $objectiveManager;
        $this->progressManager = $progressManager;
    }

    /**
     * Displays the index of the learning objectives tool, i.e.
     * the list of learning objectives.
     *
     * @EXT\Route("/", name="hevinci_objectives")
     * @EXT\Template
     *
     * @return array
     */
    public function objectivesAction()
    {
        return ['objectives' => $this->objectiveManager->listObjectives()];
    }

    /**
     * Displays the objective creation form.
     *
     * @EXT\Route("/new", name="hevinci_new_objective")
     * @EXT\Template("HeVinciCompetencyBundle:Objective:objectiveForm.html.twig")
     *
     * @return array
     */
    public function newObjectiveAction()
    {
        return ['form' => $this->formHandler->getView('hevinci_form_objective')];
    }

    /**
     * Handles the submission of the objective creation form.
     *
     * @EXT\Route("/", name="hevinci_create_objective")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Objective:objectiveForm.html.twig")
     *
     * @param Request $request
     * @return JsonResponse|array
     */
    public function createObjectiveAction(Request $request)
    {
        if ($this->formHandler->isValid('hevinci_form_objective', $request)) {
            return new JsonResponse(
                $this->objectiveManager->persistObjective($this->formHandler->getData())
            );
        }

        return ['form' => $this->formHandler->getView()];
    }

    /**
     * Deletes an objective.
     *
     * @EXT\Route("/{id}/delete", name="hevinci_delete_objective")
     *
     * @param Objective $objective
     * @return JsonResponse
     */
    public function deleteObjectiveAction(Objective $objective)
    {
        return new JsonResponse($this->objectiveManager->deleteObjective($objective));
    }

    /**
     * Displays the edition form of an objective.
     *
     * @EXT\Route("/{id}/edit", name="hevinci_objective_edit_form")
     * @EXT\Template("HeVinciCompetencyBundle:Objective:objectiveEditForm.html.twig")
     *
     * @param Objective $objective
     * @return array
     */
    public function objectiveEditionFormAction(Objective $objective)
    {
        return [
            'form' => $this->formHandler->getView('hevinci_form_objective', $objective),
            'objective' => $objective
        ];
    }

    /**
     * Edits an objective.
     *
     * @EXT\Route("/{id}/edit", name="hevinci_edit_objective")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Objective:objectiveEditForm.html.twig")
     *
     * @param Request   $request
     * @param Objective $objective
     * @return array
     */
    public function editObjectiveAction(Request $request, Objective $objective)
    {
        if ($this->formHandler->isValid('hevinci_form_objective', $request, $objective)) {
            return new JsonResponse(
                $this->objectiveManager->persistObjective($this->formHandler->getData())
            );
        }

        return ['form' => $this->formHandler->getView(), 'objective' => $objective];
    }

    /**
     * Links a competency to an objective with a given expected level.
     *
     * @EXT\Route(
     *     "/{id}/competencies/{competencyId}/levels/{levelId}",
     *     name="hevinci_objective_link_competency",
     *     requirements={"competencyId"="\d+", "levelId"="\d+"}
     * )
     * @EXT\Method("POST")
     * @EXT\ParamConverter("competency", options={"id"= "competencyId"})
     * @EXT\ParamConverter("level", options={"id"="levelId"})
     *
     * @param Objective     $objective
     * @param Competency    $competency
     * @param Level         $level
     * @return JsonResponse
     */
    public function linkCompetencyAction(Objective $objective, Competency $competency, Level $level)
    {
        return new JsonResponse(
            $result = $this->objectiveManager->linkCompetency($objective, $competency, $level),
            $result ? 200 : 204
        );
    }

    /**
     * Deletes an objective.
     *
     * @EXT\Route("/link/{id}/delete", name="hevinci_delete_objective_association")
     *
     * @param ObjectiveCompetency $link
     * @return JsonResponse
     */
    public function deleteCompetencyLinkAction(ObjectiveCompetency $link)
    {
        return new JsonResponse($this->objectiveManager->deleteCompetencyLink($link));
    }

    /**
     * Returns the competencies associated to an objective.
     *
     * @EXT\Route("/{id}/competencies", name="hevinci_load_objective_competencies")
     *
     * @param Objective $objective
     * @return JsonResponse
     */
    public function objectiveCompetenciesAction(Objective $objective)
    {
        return new JsonResponse($this->objectiveManager->loadObjectiveCompetencies($objective));
    }

    /**
     * Returns the competencies associated to an objective assigned to a user, with progress data.
     *
     * @EXT\Route("/{id}/users/{userId}/competencies", name="hevinci_load_user_objective_competencies")
     * @EXT\ParamConverter("user", options={"id"= "userId"})
     *
     * @param Objective $objective
     * @param User      $user
     * @return JsonResponse
     */
    public function userObjectiveCompetenciesAction(Objective $objective, User $user)
    {
        return new JsonResponse($this->objectiveManager->loadUserObjectiveCompetencies($objective, $user));
    }

    /**
     * Displays the list of users who have at least one objective.
     *
     * @EXT\Route("/users", name="hevinci_objectives_users")
     * @EXT\Template
     *
     * @param Request $request
     * @return array
     */
    public function usersAction(Request $request)
    {
        return ['pager' => $this->objectiveManager->listUsersWithObjective(null, $request->query->get('page', 1))];
    }

    /**
     * Displays the list of users who have a particular objective.
     *
     * @EXT\Route("/{id}/users", name="hevinci_objective_users")
     * @EXT\Template("HeVinciCompetencyBundle:Objective:users.html.twig")
     *
     * @param Objective $objective
     * @param Request   $request
     * @return array
     */
    public function objectiveUsersAction(Objective $objective, Request $request)
    {
        return [
            'pager' => $this->objectiveManager->listUsersWithObjective($objective, $request->query->get('page', 1)),
            'filterObjective' => $objective
        ];
    }

    /**
     * Displays the members of a group with their objectives.
     *
     * @EXT\Route("/groups/{id}/users", name="hevinci_objective_group_users")
     * @EXT\Template("HeVinciCompetencyBundle:Objective:users.html.twig")
     *
     * @param Group     $group
     * @param Request   $request
     * @return array
     */
    public function groupUsersAction(Group $group, Request $request)
    {
        return [
            'pager' => $this->objectiveManager->listGroupUsers($group, $request->query->get('page', 1)),
            'filterGroup' => $group
        ];
    }

    /**
     * Displays the list of groups who have at least one objective.
     *
     * @EXT\Route("/groups", name="hevinci_objectives_groups")
     * @EXT\Template
     *
     * @param Request $request
     * @return array
     */
    public function groupsAction(Request $request)
    {
        return ['pager' => $this->objectiveManager->listGroupsWithObjective(null, $request->query->get('page', 1))];
    }

    /**
     * Displays the list of groups who have a particular objective.
     *
     * @EXT\Route("/{id}/groups", name="hevinci_objective_groups")
     * @EXT\Template("HeVinciCompetencyBundle:Objective:groups.html.twig")
     *
     * @param Objective $objective
     * @return array
     */
    public function objectiveGroupsAction(Objective $objective)
    {
        return [
            'pager' => $this->objectiveManager->listGroupsWithObjective($objective),
            'filterObjective' => $objective
        ];
    }

    /**
     * Assigns an objective to a user.
     *
     * @EXT\Route("/{objectiveId}/users/{userId}", name="hevinci_objectives_assign_to_user")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("objective", options={"id"="objectiveId"})
     * @EXT\ParamConverter("user", options={"id"="userId"})
     *
     * @param Objective $objective
     * @param User $user
     * @return JsonResponse
     */
    public function assignObjectiveToUserAction(Objective $objective, User $user)
    {
        return new JsonResponse(
            $isAssigned = $this->objectiveManager->assignObjective($objective, $user),
            $isAssigned ? 200 : 204
        );
    }

    /**
     * Assigns an objective to a group.
     *
     * @EXT\Route("/{objectiveId}/groups/{groupId}", name="hevinci_objectives_assign_to_group")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("objective", options={"id"="objectiveId"})
     * @EXT\ParamConverter("group", options={"id"="groupId"})
     *
     * @param Objective $objective
     * @param Group     $group
     * @return JsonResponse
     */
    public function assignObjectiveToGroupAction(Objective $objective, Group $group)
    {
        return new JsonResponse(
            $isAssigned = $this->objectiveManager->assignObjective($objective, $group),
            $isAssigned ? 200 : 204
        );
    }

    /**
     * Returns the objectives assigned to a user.
     *
     * @EXT\Route("/users/{id}", name="hevinci_user_objectives")
     *
     * @param User $user
     * @return JsonResponse
     */
    public function loadUserObjectivesAction(User $user)
    {
        return new JsonResponse($this->objectiveManager->loadSubjectObjectives($user));
    }

    /**
     * Unassigns a user objective.
     *
     * @EXT\Route("/{objectiveId}/users/{userId}/remove", name="hevinci_remove_user_objective")
     * @EXT\ParamConverter("objective", options={"id"="objectiveId"})
     * @EXT\ParamConverter("user", options={"id"="userId"})
     *
     * @param Objective $objective
     * @param User      $user
     * @return JsonResponse
     */
    public function removeUserObjectiveAction(Objective $objective, User $user)
    {
        return new JsonResponse(
            $result = $this->objectiveManager->removeUserObjective($objective, $user),
            $result === false ? 204 : 200
        );
    }

    /**
     * Unassigns a user objective.
     *
     * @EXT\Route("/{objectiveId}/groups/{groupId}/remove", name="hevinci_remove_group_objective")
     * @EXT\ParamConverter("objective", options={"id"="objectiveId"})
     * @EXT\ParamConverter("group", options={"id"="groupId"})
     *
     * @param Objective $objective
     * @param Group     $group
     * @return JsonResponse
     */
    public function removeGroupObjectiveAction(Objective $objective, Group $group)
    {
        return new JsonResponse($this->objectiveManager->removeGroupObjective($objective, $group));
    }

    /**
     * Returns the objectives assigned to a user.
     *
     * @EXT\Route("/groups/{id}", name="hevinci_group_objectives")
     *
     * @param Group $group
     * @return JsonResponse
     */
    public function loadGroupObjectivesAction(Group $group)
    {
        return new JsonResponse($this->objectiveManager->loadSubjectObjectives($group));
    }

    /**
     * Displays the progress history of a user for a given competency.
     *
     * Note: this method belongs to this controller (and not the competency
     *       controller) because it is called from the learning objective tool
     *       and must follow its security policy.
     *
     * @EXT\Route("/users/{userId}/competencies/{id}/history", name="hevinci_competency_user_history")
     * @EXT\ParamConverter("user", options={"id"="userId"})
     * @EXT\Template("HeVinciCompetencyBundle::competencyHistory.html.twig")
     *
     * @param Competency    $competency
     * @param User          $user
     * @return array
     */
    public function competencyUserHistoryAction(Competency $competency, User $user)
    {
        return [
            'competency' => $competency,
            'user' => $user,
            'logs' => $this->progressManager->listLeafCompetencyLogs($competency, $user)
        ];
    }
}
