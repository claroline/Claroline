<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Ability;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.competency.activity_manager")
 */
class ActivityManager
{
    private $om;
    private $abilityRepo;
    private $competencyRepo;

    /**
     * @DI\InjectParams({
     *     "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
        $this->abilityRepo = $om->getRepository('HeVinciCompetencyBundle:Ability');
        $this->competencyRepo = $om->getRepository('HeVinciCompetencyBundle:Competency');
    }

    /**
     * Returns an array representation of all the competencies and abilities
     * linked to a given activity, along with their path in their competency
     * framework. Competencies and abilities are distinguished from each other
     * by the "type" key.
     *
     * @param Activity $activity
     * @return array
     */
    public function loadLinkedCompetencies(Activity $activity)
    {
        $abilities = $this->abilityRepo->findByActivity($activity);
        $result = [];

        foreach ($abilities as $ability) {
            $result[] = $this->loadAbility($ability);
        }

        return $result;
    }

    /**
     * Creates a link between an activity and an ability. If the link already
     * exists, the method returns false. Otherwise, it returns an array
     * representation of the ability.
     *
     * @param Activity $activity
     * @param Ability $ability
     * @return array|bool
     */
    public function linkActivityToAbility(Activity $activity, Ability $ability)
    {
        if ($ability->isLinkedToActivity($activity)) {
            return false;
        }

        $ability->linkActivity($activity);
        $this->om->flush();

        return $this->loadAbility($ability);
    }

    /**
     * Removes a link between an activity and an ability.
     *
     * @param Activity $activity
     * @param Ability $ability
     * @throws \LogicException if the link doesn't exists
     */
    public function removeAbilityLink(Activity $activity, Ability $ability)
    {
        if (!$ability->isLinkedToActivity($activity)) {
            throw new \LogicException(
                "There's no link between activity {$activity->getId()} and ability {$ability->getId()}"
            );
        }

        $ability->removeActivity($activity);
        $this->om->flush();
    }

    private function loadAbility(Ability $ability)
    {
        return [
            'id' => $ability->getId(),
            'name' => $ability->getName(),
            'type' => 'ability_',
            'paths' => array_map(function ($link) {
                return [
                    'level' => $link->getLevel()->getName(),
                    'steps' => array_map(function ($step) {
                        return $step->getName();
                    }, $this->competencyRepo->getPath($link->getCompetency())),
                ];
            }, $ability->getCompetencyAbilities()->toArray())
        ];
    }
}
