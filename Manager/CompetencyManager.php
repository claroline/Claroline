<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Scale;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @DI\Service("hevinci.competency.competency_manager")
 */
class CompetencyManager
{
    private $om;
    private $competencyRepo;
    private $scaleRepo;
    private $translator;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "translator" = @DI\Inject("translator")
     * })
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om, TranslatorInterface $translator)
    {
        $this->om = $om;
        $this->competencyRepo = $om->getRepository('HeVinciCompetencyBundle:Competency');
        $this->scaleRepo = $om->getRepository('HeVinciCompetencyBundle:Scale');
        $this->translator = $translator;
    }

    /**
     * Returns the list of registered frameworks.
     *
     * @return array
     */
    public function listFrameworks()
    {
        return $this->competencyRepo->findBy(['parent' => null]);
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

    /**
     * Creates a default scale if no scale exists yet.
     */
    public function ensureHasScale()
    {
        if (!$this->hasScales()) {
            $defaultScale = new Scale();
            $defaultScale->setName(
                $this->translator->trans('scale.default_name', [], 'competency')
            );
            $defaultLevel = new Level();
            $defaultLevel->setValue(0);
            $defaultLevel->setName(
                $this->translator->trans('scale.default_level_name', [], 'competency')
            );
            $defaultScale->setLevels(new ArrayCollection([$defaultLevel]));
            $this->om->persist($defaultScale);
            $this->om->flush();
        }
    }

    /**
     * Persists a competency framework.
     *
     * @param Competency $framework
     * @return Competency
     */
    public function persistFramework(Competency $framework)
    {
        $this->om->persist($framework);
        $this->om->flush();

        return $framework;
    }

    /**
     * Returns a full array representation of a framework tree.
     *
     * @param Competency $framework
     * @return array
     */
    public function loadFramework(Competency $framework)
    {
        return $this->competencyRepo->childrenHierarchy($framework, false, [], true)[0];
    }

    /**
     * Deletes a framework
     *
     * @param Competency $framework
     */
    public function deleteFramework(Competency $framework)
    {
        $this->om->remove($framework);
        $this->om->flush();
    }

    /**
     * Creates a sub-competency.
     *
     * @param Competency $parent
     * @param Competency $child
     * @return Competency
     */
    public function createSubCompetency(Competency $parent, Competency $child)
    {
        $child->setParent($parent);
        $this->om->persist($child);
        $this->om->flush();

        return $child;
    }
}
