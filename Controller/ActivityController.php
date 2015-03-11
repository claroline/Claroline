<?php

namespace HeVinci\CompetencyBundle\Controller;

use Claroline\CoreBundle\Entity\Resource\Activity;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Manager\ActivityManager;
use HeVinci\CompetencyBundle\Manager\CompetencyManager;
use JMS\DiExtraBundle\Annotation as DI;
use JMS\SecurityExtraBundle\Annotation as SEC;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as EXT;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @EXT\Route(requirements={"id"="\d+"}, options={"expose"=true})
 * @EXT\Method("GET")
 */
class ActivityController
{
    private $competencyManager;
    private $activityManager;

    /**
     * @DI\InjectParams({
     *     "competencyManager"  = @DI\Inject("hevinci.competency.competency_manager"),
     *     "activityManager"    = @DI\Inject("hevinci.competency.activity_manager")
     * })
     *
     * @param CompetencyManager $competencyManager
     * @param ActivityManager   $activityManager
     */
    public function __construct(
        CompetencyManager $competencyManager,
        ActivityManager $activityManager
    )
    {
        $this->competencyManager = $competencyManager;
        $this->activityManager = $activityManager;
    }

    /**
     * Displays the list of competencies associated with an activity.
     *
     * @EXT\Route("/activity/{id}", name="hevinci_activity_competencies")
     * @SEC\SecureParam(name="activity", permissions="OPEN")
     * @EXT\Template
     *
     * @param Activity $activity
     * @return array
     */
    public function competenciesAction(Activity $activity)
    {
        return [
            '_resource' => $activity,
            'competencies' => $this->activityManager->loadLinkedCompetencies($activity)
        ];
    }

    /**
     * Displays a list of competency frameworks to be selected.
     *
     * @EXT\Route("/activity/frameworks", name="hevinci_activity_frameworks")
     * @EXT\Template
     *
     * @return array
     */
    public function frameworksAction()
    {
        return ['frameworks' => $this->competencyManager->listFrameworks()];
    }

    /**
     * Displays a list of competencies to be selected.
     *
     * @EXT\Route("/activity/frameworks/{id}", name="hevinci_activity_framework_competencies")
     * @EXT\Template
     *
     * @param $framework Competency
     * @return array
     */
    public function frameworkCompetenciesAction(Competency $framework)
    {
        return ['framework' => $this->competencyManager->loadFramework($framework)];
    }

    /**
     * Creates an association between an activity and an ability.
     *
     * @EXT\Route("/activity/{id}/ability/{abilityId}/link", name="hevinci_activity_link_ability")
     * @EXT\Method("POST")
     * @EXT\ParamConverter("ability", options={"id"= "abilityId"})
     * @SEC\SecureParam(name="activity", permissions="OPEN")
     * @SEC\PreAuthorize("hasRole('ROLE_COMPETENCY_MANAGER')")
     *
     * @param Activity $activity
     * @param Ability $ability
     * @return JsonResponse
     */
    public function linkAbilityAction(Activity $activity, Ability $ability)
    {
        return new JsonResponse($this->activityManager->linkActivityToAbility($activity, $ability));
    }
}
