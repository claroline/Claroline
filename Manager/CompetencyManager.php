<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Scale;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.competency.competency_manager")
 */
class CompetencyManager
{
    private $om;
    private $competencyRepo;
    private $scaleRepo;

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
        $this->competencyRepo = $om->getRepository('HeVinciCompetencyBundle:Competency');
        $this->scaleRepo = $om->getRepository('HeVinciCompetencyBundle:Scale');
    }

    /**
     * Returns the list of registered frameworks.
     *
     * @return array
     */
    public function listFrameworks()
    {
        return $this->competencyRepo->findAll();
    }

    /**
     * Returns whether there are scales registered in the database.
     *
     * @return bool
     */
    public function hasScales()
    {
        return $this->om->count('HeVinciCompetencyBundle:Scale') > 0;
    }

    /**
     * Persists a scale in the database.
     *
     * @param Scale $scale
     * @return Scale
     */
    public function persistScale(Scale $scale)
    {
        $this->om->persist($scale);
        $this->om->flush();

        return $scale;
    }

    /**
     * Returns the list of scales.
     */
    public function listScales()
    {
        return $this->scaleRepo->findAll();
    }

    /**
     * Deletes a scale
     *
     * @param Scale $scale
     */
    public function deleteScale(Scale $scale)
    {
        if ($scale->isLocked()) {
            throw new \LogicException(
                "Cannot delete scale '{$scale->getName()}': scale is locked"
            );
        }

        $this->om->remove($scale);
        $this->om->flush();
    }
}
