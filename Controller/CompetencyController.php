<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use HeVinci\CompetencyBundle\Form\ScaleType;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("canOpenAdminTool('competencies')")
 */
class CompetencyController
{
    private $competencyManager;
    private $formFactory;
    private $templating;

    /**
     * @DI\InjectParams({
     *     "manager" = @DI\Inject("hevinci.competency.competency_manager"),
     *     "factory" = @DI\Inject("form.factory"),
     *     "engine"  = @DI\Inject("templating")
     * })
     *
     * @param CompetencyManager $manager
     */
    public function __construct(
        CompetencyManager $manager,
        FormFactory $factory,
        EngineInterface $engine
    )
    {
        $this->competencyManager = $manager;
        $this->formFactory = $factory;
        $this->templating = $engine;
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
            'frameworks' => $this->competencyManager->listFrameworks(),
            'hasScales' => $this->competencyManager->hasScales()
        ];
    }

    /**
     * Displays the scale creation form.
     *
     * @EXT\Route("/scales/new", name="hevinci_new_scale", options={"expose"=true})
     * @EXT\Method("GET")
     * @EXT\Template
     *
     * @return array
     */
    public function newScaleAction()
    {
        return [
            'form' => $this->formFactory->create(new ScaleType())->createView()
        ];
    }

    /**
     * Handles the scale creation form submission.
     *
     * @EXT\Route("/scales", name="hevinci_create_scale", options={"expose"=true})
     * @EXT\Method("POST")
     *
     * @return JsonResponse|array
     */
    public function createScaleAction(Request $request)
    {
        $form = $this->formFactory->create(new ScaleType(), new Scale());
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->competencyManager->createScale($form->getData());

            return new JsonResponse();
        }

        return new Response(
            $this->templating->render(
                'HeVinciCompetencyBundle:Competency:newScale.html.twig',
                ['form' => $form->createView()]
            )
        );
    }
}
