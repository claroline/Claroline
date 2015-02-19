<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Scale;
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
 * @EXT\Route("/scales", requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class ScaleController
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
     * Displays the scale creation form.
     *
     * @EXT\Route("/new", name="hevinci_new_scale")
     * @EXT\Template("HeVinciCompetencyBundle:Scale:form.html.twig")
     *
     * @return array
     */
    public function newScaleAction()
    {
        return ['form' => $this->formHandler->getView('hevinci_form_scale')];
    }

    /**
     * Handles the scale creation form submission.
     *
     * @EXT\Route("/", name="hevinci_create_scale")
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Scale:form.html.twig")
     *
     * @param Request $request
     * @return array|JsonResponse
     */
    public function createScaleAction(Request $request)
    {
        if ($this->formHandler->isValid('hevinci_form_scale', $request)) {
            return new JsonResponse(
                $this->competencyManager->persistScale($this->formHandler->getData())
            );
        } else

        return ['form' => $this->formHandler->getView()];
    }

    /**
     * Displays the list of scales.
     *
     * @EXT\Route("/", name="hevinci_scales")
     * @EXT\Template
     *
     * @return array
     */
    public function scalesAction()
    {
        return ['scales' => $this->competencyManager->listScales()];
    }

    /**
     * Displays a scale, either in read-only or in edit mode.
     *
     * @EXT\Route("/{id}/{edit}", name="hevinci_scale", defaults={"edit"=0}))
     * @EXT\Template("HeVinciCompetencyBundle:Scale:editForm.html.twig")
     *
     * @param Scale $scale
     * @param bool  $edit
     * @return array
     */
    public function scaleAction(Scale $scale, $edit)
    {
        return [
            'form' => $this->formHandler->getView(
                'hevinci_form_scale',
                $scale,
                ['read_only' => $edit == 0]
            ),
            'scale' => $edit == 0 ? null : $scale
        ];
    }

    /**
     * Updates a scale.
     *
     * @EXT\Route("/{id}", name="hevinci_edit_scale", defaults={"edit"=0})
     * @EXT\Method("POST")
     * @EXT\Template("HeVinciCompetencyBundle:Scale:editForm.html.twig")
     *
     * @param Request   $request
     * @param Scale     $scale
     * @return array
     */
    public function editScaleAction(Request $request, Scale $scale)
    {
        if ($this->formHandler->isValid('hevinci_form_scale', $request, $scale)) {
            return new JsonResponse(
                $this->competencyManager->persistScale($this->formHandler->getData())
            );
        }

        return ['form' => $this->formHandler->getView(), 'scale' => $scale];
    }

    /**
     * Deletes a scale.
     *
     * @EXT\Route("/delete/{id}", name="hevinci_delete_scale")
     *
     * @param Scale $scale
     * @return JsonResponse
     */
    public function deleteScaleAction(Scale $scale)
    {
        return new JsonResponse($this->competencyManager->deleteScale($scale));
    }
}
