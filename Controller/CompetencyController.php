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
 * @EXT\Route("/frameworks", requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class CompetencyController
{
    private $competencyManager;
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
        $this->competencyManager = $manager;
        $this->formHandler = $handler;
    }

    /**
     * Displays the index of the competency tool, i.e. the list
     * of competency frameworks.
     *
     * @EXT\Route("/", name="hevinci_frameworks")
     * @EXT\Template
     *
     * @return array
     */
    public function frameworksAction()
    {
        return [
            'frameworks' => $this->competencyManager->listFrameworks(),
            'hasScales' => $this->competencyManager->hasScales()
        ];
    }

    /**
     * Displays the framework creation form. If no scale has been created yet,
     * creates a default scale on the fly first.
     *
     * @EXT\Route("/new", name="hevinci_new_framework")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:frameworkForm.html.twig")
     *
     * @return array
     */
    public function newFrameworkAction()
    {
        $this->competencyManager->ensureHasScale();

        return [
            'form' => $this->formHandler->getView('hevinci_form_framework')
        ];
    }

    /**
     * Handles the framework creation form submission.
     *
     * @EXT\Route("/", name="hevinci_create_framework")
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
                $this->competencyManager->persistFramework($this->formHandler->getData())
            );
        }

        return ['form' => $this->formHandler->getView()];
    }

    /**
     * Displays the management page for a given framework.
     *
     * @EXT\Route("/{id}", name="hevinci_framework")
     * @EXT\Template
     *
     * @param Competency $framework
     * @return array
     */
    public function frameworkAction(Competency $framework)
    {
        return ['framework' => $this->competencyManager->loadFramework($framework)];
    }

    /**
     * Deletes a framework.
     *
     * @EXT\Route("/delete/{id}", name="hevinci_delete_framework")
     *
     * @param Competency $framework
     * @return JsonResponse
     */
    public function deleteAction(Competency $framework)
    {
        return new JsonResponse($this->competencyManager->deleteFramework($framework));
    }

    /**
     * Displays the competency creation form.
     *
     * @EXT\Route("/parent/{id}/new", name="hevinci_new_competency")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyForm.html.twig")
     *
     * @param Competency $parent
     * @return array
     */
    public function newSubCompetencyAction(Competency $parent)
    {
        return [
            'form' => $this->formHandler->getView('hevinci_form_competency'),
            'parentId' => $parent->getId()
        ];
    }

    /**
     * Creates a sub-competency.
     *
     * @EXT\Route("/parent/{id}", name="hevinci_create_competency")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Competency:competencyForm.html.twig")
     *
     * @param Request $request
     * @param Competency $parent
     * @return array|JsonResponse
     */
    public function createSubCompetency(Request $request, Competency $parent)
    {
        if ($this->formHandler->isValid('hevinci_form_competency', $request)) {
            return new JsonResponse(
                $this->competencyManager->createSubCompetency($parent, $this->formHandler->getData())
            );
        }

        return [
            'form' => $this->formHandler->getView(),
            'parentId' => $parent->getId()
        ];
    }
}
