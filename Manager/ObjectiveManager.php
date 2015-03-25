<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Objective;
use HeVinci\CompetencyBundle\Entity\ObjectiveCompetency;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.competency.objective_manager")
 */
class ObjectiveManager
{
    private $om;
    private $repo;

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
        $this->repo = $om->getRepository('HeVinciCompetencyBundle:Objective');
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

    /**
     * Returns the list of existing objectives.
     */
    public function listObjectives()
    {
        return $this->repo->findAll();
    }

    /**
     * Deletes an objective.
     *
     * @param Objective $objective
     */
    public function deleteObjective(Objective $objective)
    {
        $this->om->remove($objective);
        $this->om->flush();
    }

    /**
     * Creates an association between an objective and a competency,
     * with an expected level.
     *
     * @param Objective     $objective
     * @param Competency    $competency
     * @param Level         $level
     */
    public function linkCompetency(Objective $objective, Competency $competency, Level $level)
    {
        // check level scale == competency root scale

        $link = new ObjectiveCompetency();
        $link->setObjective($objective);
        $link->setCompetency($competency);
        $link->setLevel($level);

        $this->om->persist($link);
        $this->om->flush();
    }
}
