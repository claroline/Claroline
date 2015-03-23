<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Objective;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.competency.objective_manager")
 */
class ObjectiveManager
{
    private $om;

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
    }

    /**
     * Persists a learning objective.
     *
     * @param Objective $objective
     * @return Objective
     */
    public function persistObjective(Objective $objective)
    {
        $this->om->persist($objective);
        $this->om->flush();

        return $objective;
    }
}

