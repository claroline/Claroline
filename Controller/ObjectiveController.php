<?php

namespace HeVinci\CompetencyBundle\Controller;

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
     * @return array
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
     * Displays the index of the learning objectives tool, i.e.
     * the list of learning objectives.
     *
     * @EXT\Route("/users", name="hevinci_objectives_users")
     * @EXT\Template
     *
     * @return array
     */
    public function usersAction()
    {
        return [];
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
