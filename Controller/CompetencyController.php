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
}
