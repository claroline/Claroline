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

    public function loadLinkedCompetencies(Activity $activity)
    {
        $abilities = $this->abilityRepo->findByActivity($activity);
        $result = [];

        foreach ($abilities as $ability) {
            $properties = [
                'name' => $ability->getName(),
                'type' => 'ability_',
                'paths' => []
            ];

            foreach ($ability->getCompetencyAbilities() as $link) {
                $path = $this->competencyRepo->getPath($link->getCompetency());
                $steps = [];

                // use array_map instead...

                foreach ($path as $step) {
                    $steps[] = $step->getName();
                }

                $properties['paths'][] = [
                    'level' => $link->getLevel()->getName(),
                    'steps' => $steps,
                ];
            }

            $result[] = $properties;
        }

        return $result;
    }

    public function linkActivityToAbility(Activity $activity, Ability $ability)
    {
        $ability->linkActivity($activity);
        $this->om->flush();

        // return ability + path
    }
}
