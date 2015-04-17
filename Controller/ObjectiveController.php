<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Entity\ObjectiveCompetency;
use HeVinci\CompetencyBundle\Form\Handler\FormHandler;
use HeVinci\CompetencyBundle\Manager\ObjectiveManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('learning-objectives')")
 * @EXT\Route("/objectives", requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class ObjectiveController
{
    private $formHandler;
    private $manager;

    /**
     * @DI\InjectParams({
     *     "handler" = @DI\Inject("hevinci.form.handler"),
     *     "manager" = @DI\Inject("hevinci.competency.objective_manager"),
     * })
     *
     * @param FormHandler       $handler
     * @param ObjectiveManager  $manager
     */
    public function __construct(FormHandler $handler, ObjectiveManager $manager)
    {
        $this->formHandler = $handler;
        $this->manager = $manager;
    }

    /**
     * Displays the index of the learning objectives tool, i.e.
     * the list of learning objectives.
     *
     * @EXT\Route("/", name="hevinci_objectives_index")
     * @EXT\Template
     *
     * @return array
     */
    public function objectivesAction()
    {
        return ['objectives' => $this->manager->listObjectives()];
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
                $this->manager->persistObjective($this->formHandler->getData())
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
        return new JsonResponse($this->manager->deleteObjective($objective));
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
                $this->manager->persistObjective($this->formHandler->getData())
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
     * @EXT\ParamConverter("level", options={"id"= "levelId"})
     *
     * @param Objective     $objective
     * @param Competency    $competency
     * @param Level         $level
     * @return JsonResponse
     */
    public function linkCompetencyAction(Objective $objective, Competency $competency, Level $level)
    {
        return new JsonResponse(
            $result = $this->manager->linkCompetency($objective, $competency, $level),
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
        return new JsonResponse($this->manager->deleteCompetencyLink($link));
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
        return new JsonResponse($this->manager->loadObjectiveCompetencies($objective));
    }

    /**
     * Displays the list of users who have at least one objective.
     *
     * @EXT\Route("/users", name="hevinci_objectives_users")
     * @EXT\Template
     *
     * @return array
     */
    public function usersAction()
    {
        return ['pager' => $this->manager->listUsersWithObjective()];
    }

    /**
     * Displays the index of the learning objectives tool, i.e.
     * the list of learning objectives.
     *
     * @EXT\Route("/groups", name="hevinci_objectives_groups")
     * @EXT\Template
     *
     * @return array
     */
    public function groupsAction()
    {
        return [];
    }
}
