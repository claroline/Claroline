<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Form\Handler\FormHandler;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('competencies')")
 * @EXT\Route(requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class CompetencyController
{
    private $manager;
    private $formHandler;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("hevinci.competency.competency_manager"),
     *     "handler" = @DI\Inject("hevinci.form.handler")
     * })
     *
     * @param CompetencyManager $manager
     * @param FormHandler       $handler
     */
    public function __construct(
        CompetencyManager $manager,
        FormHandler $handler
    )
    {
        $this->manager = $manager;
        $this->formHandler = $handler;
    }

    /**
     * Displays the index of the competency tool, i.e. the list
     * of competency frameworks.
     *
     * @EXT\Route("/frameworks", name="hevinci_frameworks")
     * @EXT\Template
     *
     * @return array
     */
    public function frameworksAction()
    {
        return [
            'frameworks' => $this->manager->listFrameworks(),
            'hasScales' => $this->manager->hasScales()
        ];
    }

    /**
     * Displays the framework creation form. If no scale has been created yet,
     * creates a default scale on the fly first.
     *
     * @EXT\Route("/frameworks/new", name="hevinci_new_framework")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:frameworkForm.html.twig")
     *
     * @return array
     */
    public function newFrameworkAction()
    {
        $this->manager->ensureHasScale();

        return ['form' => $this->formHandler->getView('hevinci_form_framework')];
    }

    /**
     * Handles the framework creation form submission.
     *
     * @EXT\Route("/frameworks", name="hevinci_create_framework")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:frameworkForm.html.twig")
     *
     * @param Request $request
     * @return array|JsonResponse
     */
    public function createFrameworkAction(Request $request)
    {
        if ($this->formHandler->isValid('hevinci_form_framework', $request)) {
            return new JsonResponse(
                $this->manager->persistFramework($this->formHandler->getData())
            );
        }

        return ['form' => $this->formHandler->getView()];
    }

    /**
     * Displays the management page for a given framework.
     *
     * @EXT\Route("/frameworks/{id}", name="hevinci_framework")
     * @EXT\Template
     *
     * @param Competency $framework
     * @return array
     */
    public function frameworkAction(Competency $framework)
    {
        return ['framework' => $this->manager->loadFramework($framework)];
    }

    /**
     * Displays the framework edition form.
     *
     * @EXT\Route("/frameworks/{id}/edit", name="hevinci_edit_framework_form")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:frameworkEditForm.html.twig")
     *
     * @param Competency $framework
     * @return array
     */
    public function frameworkEditionFormAction(Competency $framework)
    {
        $this->manager->ensureIsRoot($framework);

        return [
            'form' => $this->formHandler->getView('hevinci_form_framework', $framework),
            'framework' => $framework
        ];
    }

    /**
     * Edits a framework.
     *
     * @EXT\Route("/frameworks/{id}", name="hevinci_edit_framework")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:frameworkEditForm.html.twig")
     *
     * @param Request       $request
     * @param Competency    $framework
     * @return array
     */
    public function editFrameworkAction(Request $request, Competency $framework)
    {
        $this->manager->ensureIsRoot($framework);

        if ($this->formHandler->isValid('hevinci_form_framework', $request, $framework)) {
            return new JsonResponse($this->manager->updateCompetency($framework));
        }

        return ['form' => $this->formHandler->getView(), 'framework' => $framework];
    }

    /**
     * Deletes a competency.
     *
     * @EXT\Route("/{id}/delete", name="hevinci_delete_competency")
     *
     * @param Competency $competency
     * @return JsonResponse
     */
    public function deleteCompetencyAction(Competency $competency)
    {
        return new JsonResponse($this->manager->deleteCompetency($competency));
    }

    /**
     * Displays the competency creation form.
     *
     * @EXT\Route("/{id}/sub", name="hevinci_new_competency")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyForm.html.twig")
     *
     * @param Competency $parent
     * @return array
     */
    public function newSubCompetencyAction(Competency $parent)
    {
        return [
            'form' => $this->formHandler->getView('hevinci_form_competency', null, ['parent_competency' => $parent]),
            'parentId' => $parent->getId()
        ];
    }

    /**
     * Creates a sub-competency.
     *
     * @EXT\Route("/{id}/sub", name="hevinci_create_competency")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyForm.html.twig")
     *
     * @param Request $request
     * @param Competency $parent
     * @return array|JsonResponse
     */
    public function createSubCompetencyAction(Request $request, Competency $parent)
    {
        if ($this->formHandler->isValid('hevinci_form_competency', $request, null, ['parent_competency' => $parent])) {
            return new JsonResponse(
                $this->manager->createSubCompetency($parent, $this->formHandler->getData())
            );
        }

        return ['form' => $this->formHandler->getView(), 'parentId' => $parent->getId()];
    }

    /**
     * Displays the competency view/edit form.
     *
     * @EXT\Route("/{id}/edit", name="hevinci_competency")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyEditForm.html.twig")
     *
     * @param Competency $competency
     * @return array
     */
    public function competencyAction(Competency $competency)
    {
        return [
            'form' => $this->formHandler->getView('hevinci_form_competency', $competency),
            'id' => $competency->getId()
        ];
    }

    /**
     * Edits a competency.
     *
     * @EXT\Route("/{id}", name="hevinci_edit_competency")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyEditForm.html.twig")
     *
     * @param Request       $request
     * @param Competency    $competency
     * @return array
     */
    public function editCompetencyAction(Request $request, Competency $competency)
    {
        if ($this->formHandler->isValid('hevinci_form_competency', $request, $competency)) {
            return new JsonResponse($this->manager->updateCompetency($competency));
        }

        return ['form' => $this->formHandler->getView(), 'id' => $competency->getId()];
    }

    /**
     * Displays the ability creation form.
     *
     * @EXT\Route("/{id}/ability/new", name="hevinci_new_ability")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:abilityForm.html.twig")
     *
     * @param Competency $parent
     * @return array
     */
    public function newAbilityAction(Competency $parent)
    {
        return [
            'form' => $this->formHandler->getView('hevinci_form_ability', null, ['competency' => $parent]),
            'competency' => $parent
        ];
    }

    /**
     * Creates a new ability.
     *
     * @EXT\Route("/{id}/ability", name="hevinci_create_ability")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:abilityForm.html.twig")
     *
     * @param Request       $request
     * @param Competency    $parent
     * @return array
     */
    public function createAbilityAction(Request $request, Competency $parent)
    {
        if ($this->formHandler->isValid('hevinci_form_ability', $request, null, ['competency' => $parent])) {
            return new JsonResponse($this->manager->createAbility(
                $parent,
                $this->formHandler->getData(),
                $this->formHandler->getData()->getLevel()
            ));
        }

        return ['form' => $this->formHandler->getView(), 'competency' => $parent];
    }
}
