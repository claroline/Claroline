<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Entity\Resource\Activity;
use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
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
        $competencies = $this->competencyRepo->findByActivity($activity);
        $result = [];

        foreach ($abilities as $ability) {
            $result[] = $this->loadAbility($ability);
        }

        foreach ($competencies as $competency) {
            $result[] = $this->loadCompetency($competency);
        }

        return $result;
    }

    /**
     * Creates a link between an activity and an ability or a competency.
     * If the link already exists, the method returns false. Otherwise, it
     * returns an array representation of the ability/competency.
     *
     * @param Activity              $activity
     * @param Ability|Competency    $target
     * @return array|bool
     * @throws \InvalidArgumentException if the target isn't an instance of Ability or Competency
     */
    public function createLink(Activity $activity, $target)
    {
        if (!$target instanceof Ability && !$target instanceof Competency) {
            throw new \InvalidArgumentException(
                'Second argument must be a Competency or an Ability instance'
            );
        }

        if ($target->isLinkedToActivity($activity)) {
            return false;
        }

        $target->linkActivity($activity);
        $this->om->flush();

        $loadMethod = $target instanceof Competency ? 'loadCompetency' : 'loadAbility';

        return $this->{$loadMethod}($target);
    }

    /**
     * Removes a link between an activity and an ability or a competency.
     *
     * @param Activity $activity
     * @param Ability|Competency $target
     * @throws \InvalidArgumentException if the target isn't an instance of Ability or Competency
     * @throws \LogicException if the link doesn't exists
     */
    public function removeLink(Activity $activity, $target)
    {
        if (!$target instanceof Ability && !$target instanceof Competency) {
            throw new \InvalidArgumentException(
                'Second argument must be a Competency or an Ability instance'
            );
        }

        if (!$target->isLinkedToActivity($activity)) {
            throw new \LogicException(
                "There's no link between activity {$activity->getId()} and target {$target->getId()}"
            );
        }

        $target->removeActivity($activity);
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

    private function loadCompetency(Competency $competency)
    {
        return [
            'id' => $competency->getId(),
            'name' => $competency->getName(),
            'type' => 'competency_',
            'paths' => [[
                'level' => '-',
                'steps' => array_map(function ($step) {
                    return $step->getName();
                }, $this->competencyRepo->getPath($competency))
            ]]
        ];
    }
}
