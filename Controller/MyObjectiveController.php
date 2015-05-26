<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Manager\ObjectiveManager;
use HeVinci\CompetencyBundle\Manager\ProgressManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @DI\Tag("security.secure_service")
 * @SEC\PreAuthorize("hasRole('ROLE_USER')")
 * @EXT\Route("/my-objectives", requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class MyObjectiveController
{
    private $objectiveManager;
    private $progressManager;

    /**
     * @DI\InjectParams({
     *     "objectiveManager"   = @DI\Inject("hevinci.competency.objective_manager"),
     *     "progressManager"    = @DI\Inject("hevinci.competency.progress_manager")
     * })
     *
     * @param ObjectiveManager  $objectiveManager
     * @param ProgressManager   $progressManager
     */
    public function __construct(ObjectiveManager $objectiveManager, ProgressManager $progressManager)
    {
        $this->objectiveManager = $objectiveManager;
        $this->progressManager = $progressManager;
    }

    /**
     * Displays the index of the learner version of the learning
     * objectives tool, i.e the list of his learning objectives.
     *
     * @EXT\Route("/", name="hevinci_my_objectives_index")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     * @EXT\Template
     *
     * @param User $user
     * @return array
     */
    public function objectivesAction(User $user)
    {
        return [
            'objectives' => $this->objectiveManager->loadSubjectObjectives($user),
            'user' => $user
        ];
    }

    /**
     * Returns the competencies associated with an objective assigned to a user, with progress data.
     *
     * @EXT\Route("/{id}/competencies", name="hevinci_load_my_objective_competencies")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
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
     * Displays the progress history of a user for a given competency.
     *
     * @EXT\Route("/competencies/{id}/history", name="hevinci_competency_my_history")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
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
