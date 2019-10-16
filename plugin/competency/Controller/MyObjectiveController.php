<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\User;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use HeVinci\CompetencyBundle\Manager\ObjectiveManager;
use HeVinci\CompetencyBundle\Manager\ProgressManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * todo: use firewall instead for security, check if role user for route /my-objectives.
 *
 * @EXT\Route("/my-objectives", requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class MyObjectiveController
{
    private $competencyManager;
    private $objectiveManager;
    private $progressManager;

    /**
     * @param CompetencyManager $competencyManager
     * @param ObjectiveManager  $objectiveManager
     * @param ProgressManager   $progressManager
     */
    public function __construct(
        CompetencyManager $competencyManager,
        ObjectiveManager $objectiveManager,
        ProgressManager $progressManager
    ) {
        $this->competencyManager = $competencyManager;
        $this->objectiveManager = $objectiveManager;
        $this->progressManager = $progressManager;
    }

    /**
     * Fetches data for competency page of My Objectives tool.
     *
     * @EXT\Route(
     *     "/objective/{objective}/competency/{competency}",
     *     name="hevinci_my_objectives_competency"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * @param Objective  $objective
     * @param Competency $competency
     * @param User       $user
     *
     * @return JsonResponse
     */
    public function objectiveCompetencyAction(Objective $objective, Competency $competency, User $user)
    {
        $progress = $this->progressManager->getCompetencyProgress($competency, $user, false);
        $rootComptency = empty($competency->getParent()) ?
            $competency :
            $this->competencyManager->getCompetencyById($competency->getRoot());
        $scale = $rootComptency->getScale();
        $nbLevels = count($scale->getLevels());
        $acquiredLevel = empty($progress->getLevel()) ? null : $progress->getLevel()->getValue();
        $nextLevel = is_null($acquiredLevel) ?
            null :
            $this->competencyManager->getLevelByScaleAndValue($scale, $acquiredLevel + 1);
        $requiredLevel = 0;
        $objectiveComps = $objective->getObjectiveCompetencies();

        foreach ($objectiveComps as $objectiveComp) {
            $comp = $objectiveComp->getCompetency();

            if ($comp->getRoot() === $competency->getRoot() &&
                $comp->getLeft() <= $competency->getLeft() &&
                $comp->getRight() >= $competency->getRight() &&
                $objectiveComp->getLevel()->getValue() > $requiredLevel
            ) {
                $requiredLevel = $objectiveComp->getLevel()->getValue();
            }
        }

        if (is_null($acquiredLevel)) {
            $currentLevel = floor(($requiredLevel) / 2);
        } else {
            $currentLevel = is_null($nextLevel) ? $acquiredLevel : $acquiredLevel + 1;
        }
        $challenge = $this->objectiveManager->getUserChallengeByLevel($user, $competency, $currentLevel);

        return new JsonResponse([
            'objective' => $objective,
            'competency' => $competency,
            'progress' => $progress,
            'nbLevels' => $nbLevels,
            'currentLevel' => $currentLevel,
            'challenge' => $challenge,
        ]);
    }

    /**
     * Fetches data for competency page of My Objectives tool.
     *
     * @EXT\Route(
     *     "/objective/competency/{competency}/level/{level}",
     *     name="hevinci_my_objectives_competency_level"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * @param Competency $competency
     * @param int        $level
     * @param User       $user
     *
     * @return JsonResponse
     */
    public function objectiveCompetencyLevelAction(Competency $competency, $level, User $user)
    {
        $challenge = $this->objectiveManager->getUserChallengeByLevel($user, $competency, $level);

        return new JsonResponse([
            'currentLevel' => intval($level),
            'challenge' => $challenge,
        ]);
    }

    /**
     * Fetches a resource for a competency at the given level for My Objectives tool.
     *
     * @EXT\Route(
     *     "/objective/competency/{competency}/level/{level}/resource/fetch",
     *     name="hevinci_my_objectives_competency_resource_fetch"
     * )
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * @param Competency $competency
     * @param int        $level
     * @param User       $user
     *
     * @return JsonResponse
     */
    public function objectiveCompetencyResourceFetchAction(Competency $competency, $level, User $user)
    {
        $rootComptency = empty($competency->getParent()) ?
            $competency :
            $this->competencyManager->getCompetencyById($competency->getRoot());
        $scale = $rootComptency->getScale();
        $levelEntity = $this->competencyManager->getLevelByScaleAndValue($scale, $level);
        $resource = $this->objectiveManager->getRelevantResourceForUserByLevel($user, $competency, $levelEntity);
        $data = is_null($resource) ? null : ['resourceId' => $resource->getId()];

        return new JsonResponse($data);
    }

    /**
     * Returns the competencies associated with an objective assigned to a user, with progress data.
     *
     * @EXT\Route("/{id}/competencies", name="hevinci_load_my_objective_competencies")
     * @EXT\ParamConverter("user", options={"authenticatedUser"=true})
     *
     * @param Objective $objective
     * @param User      $user
     *
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
     * @param Competency $competency
     * @param User       $user
     *
     * @return array
     */
    public function competencyUserHistoryAction(Competency $competency, User $user)
    {
        return [
            'competency' => $competency,
            'user' => $user,
            'logs' => $this->progressManager->listLeafCompetencyLogs($competency, $user),
        ];
    }
}
