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
    private $competencyManager;
    private $objectiveRepo;
    private $competencyRepo;
    private $objectiveCompetencyRepo;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "manager"    = @DI\Inject("hevinci.competency.competency_manager")
     * })
     *
     * @param ObjectManager     $om
     * @param CompetencyManager $manager
     */
    public function __construct(ObjectManager $om, CompetencyManager $manager)
    {
        $this->om = $om;
        $this->competencyManager = $manager;
        $this->objectiveRepo = $om->getRepository('HeVinciCompetencyBundle:Objective');
        $this->competencyRepo = $om->getRepository('HeVinciCompetencyBundle:Competency');
        $this->objectiveCompetencyRepo = $om->getRepository('HeVinciCompetencyBundle:ObjectiveCompetency');
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
        return $this->objectiveRepo->findWithCompetencyCount();
    }

    /**
     * Returns an array representation of all the competencies
     * associated with an objective, including sub-competencies
     * and abilities, if any.
     *
     * @param Objective $objective
     * @return array
     */
    public function loadObjectiveCompetencies(Objective $objective)
    {
        $links = $objective->getObjectiveCompetencies();
        $result = [];

        foreach ($links as $link) {
            $loaded = $this->competencyManager->loadCompetency($link->getCompetency());
            $loaded['id'] = $link->getId(); // link is treated as the competency itself on client-side
            $loaded['framework'] = $link->getFramework()->getName();
            $loaded['level'] = $link->getLevel()->getName();
            $result[] = $loaded;
        }

        return $result;
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
     * with an expected level. Returns a full array representation of
     * the newly associated competency if the link doesn't already exist.
     * Otherwise, returns false.
     *
     * @param Objective     $objective
     * @param Competency    $competency
     * @param Level         $level
     * @return mixed array|bool
     * @throws \LogicException if the level doesn't belong to the root competency scale
     */
    public function linkCompetency(Objective $objective, Competency $competency, Level $level)
    {
        $link = $this->objectiveCompetencyRepo->findOneBy([
            'competency' => $competency,
            'objective' => $objective
        ]);

        if ($link) {
            return false;
        }

        $framework = $this->competencyRepo->findOneBy(['root' => $competency->getRoot()]);

        if ($level->getScale() !== $framework->getScale()) {
            throw new \LogicException(
                'Objective level must belong to the root competency scale'
            );
        }

        $link = new ObjectiveCompetency();
        $link->setObjective($objective);
        $link->setCompetency($competency);
        $link->setLevel($level);
        $link->setFramework($framework);

        $this->om->persist($link);
        $this->om->flush();

        $competency = $this->competencyManager->loadCompetency($competency);
        $competency['id'] = $link->getId(); // link is treated as the competency itself on client-side
        $competency['framework'] = $framework->getName();
        $competency['level'] = $level->getName();

        return $competency;
    }

    public function deleteCompetencyLink(ObjectiveCompetency $link)
    {
        $this->om->remove($link);
        $this->om->flush();
    }
}
