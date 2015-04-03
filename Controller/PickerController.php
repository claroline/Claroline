<?php

namespace HeVinci\CompetencyBundle\Controller;

use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_COMPETENCY_MANAGER')")
 * @EXT\Route("/picker", options={"expose"=true})
 * @EXT\Method("GET")
 */
class PickerController
{
    private $competencyManager;

    /**
     * @DI\InjectParams({
     *     "competencyManager"  = @DI\Inject("hevinci.competency.competency_manager")
     * })
     *
     * @param CompetencyManager $competencyManager
     */
    public function __construct(CompetencyManager $competencyManager)
    {
        $this->competencyManager = $competencyManager;
    }

    /**
     * Displays the list of competency frameworks for selection.
     *
     * @EXT\Route("/", name="hevinci_pick_framework")
     * @EXT\Template
     *
     * @return array
     */
    public function frameworksAction()
    {
        return ['frameworks' => $this->competencyManager->listFrameworks()];
    }

    /**
     * Displays the list of competencies of a framework for selection.
     *
     * @EXT\Route(
     *     "/framework/{id}/{loadAbilities}",
     *     name="hevinci_pick_competency",
     *     requirements={"loadAbilities" = "[01]"},
     *     defaults={"loadAbilities" = 1}
     * )
     * @EXT\Template
     *
     * @param Competency    $framework      The framework to load
     * @param bool          $loadAbilities  Whether linked abilities should be included
     * @return array
     */
    public function competenciesAction(Competency $framework, $loadAbilities)
    {
        return [
            'framework' => $this->competencyManager->loadCompetency(
                $framework,
                (bool) $loadAbilities
            )
        ];
    }

    /**
     * Displays the scale levels of a given competency framework for selection.
     *
     * @EXT\Route("/framework/{id}/levels", name="hevinci_pick_level")
     * @EXT\Template
     *
     * @param Competency $framework
     * @return array
     * @throws \LogicException if the competency is not a framework root
     */
    public function levelsAction(Competency $framework)
    {
        if ($framework->getRoot() !== $framework->getId()) {
            throw new \LogicException('Scales are only linked to root competencies');
        }

        return ['scale' => $framework->getScale()];
    }
}
