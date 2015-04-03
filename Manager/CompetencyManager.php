<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\CoreBundle\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
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
    private $abilityRepo;
    private $competencyAbilityRepo;
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
        $this->abilityRepo = $om->getRepository('HeVinciCompetencyBundle:Ability');
        $this->competencyAbilityRepo = $om->getRepository('HeVinciCompetencyBundle:CompetencyAbility');
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
    public function createScale(Scale $scale)
    {
        $this->om->persist($scale);
        $this->om->flush();

        return $scale;
    }

    /**
     * Updates an existing scale.
     *
     * @param Scale $scale
     * @return Scale
     * @throws \LogicException if the scale is already bound to an ability
     */
    public function updateScale(Scale $scale)
    {
        if ($this->scaleRepo->findAbilityCount($scale) > 0) {
            throw new \LogicException(
                "Cannot update scale '{$scale->getName()}': scale is bound to at least one ability"
            );
        }

        $this->om->flush();
        $scale->setAbilityCount(0);
        $scale->setFrameworkCount($this->scaleRepo->findCompetencyCount($scale));

        return $scale;
    }

    /**
     * Returns the list of scales.
     */
    public function listScales()
    {
        return $this->scaleRepo->findWithStatus();
    }

    /**
     * Deletes a scale.
     *
     * @param Scale $scale
     * @throws \LogicException is the scale is bound to a framework
     */
    public function deleteScale(Scale $scale)
    {
        if ($this->scaleRepo->findCompetencyCount($scale) > 0) {
            throw new \LogicException(
                "Cannot delete scale '{$scale->getName()}': scale is bound to at least one framework"
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
     * Returns a full array representation of a competency tree. Children
     * competencies and linked abilities are respectively stored under the
     * "__children" and "__abilities" keys of their corresponding competency
     * array.
     *
     * @param Competency    $competency     The competency to be loaded
     * @param bool          $loadAbilities  Whether linked abilities should be included
     * @return array
     */
    public function loadCompetency(Competency $competency, $loadAbilities = true)
    {
        $competencies = $this->competencyRepo->childrenHierarchy($competency, false, [], true)[0];

        if (!$loadAbilities) {
            return $competencies;
        }

        $abilities = $this->abilityRepo->findByCompetency($competency);
        $abilitiesByCompetency = [];

        foreach ($abilities as $ability) {
            $abilitiesByCompetency[$ability['competencyId']][] = $ability;
        }

        $augment = function ($collection, \Closure $callback) use (&$augment) {
            if (is_array($collection)) {
                $result = [];

                foreach ($collection as $key => $item) {
                    $result[$key] = $augment($item, $callback);
                }

                return $callback($result);
            }

            return $collection;
        };

        return $augment($competencies, function ($collection) use ($abilitiesByCompetency) {
            if (isset($collection['id']) && isset($abilitiesByCompetency[$collection['id']])) {
                $collection['__abilities'] = $abilitiesByCompetency[$collection['id']];
            }

            return $collection;
        });
    }

    /**
     * Ensures a competency is the root of the framework.
     *
     * @param Competency $competency
     * @throws \LogicException
     */
    public function ensureIsRoot(Competency $competency)
    {
        if ($competency->getRoot() !== $competency->getId()) {
            throw new \LogicException('Framework edition must be done on the root competency');
        }
    }

    /**
     * Deletes a competency.
     *
     * @param Competency $competency
     */
    public function deleteCompetency(Competency $competency)
    {
        $this->om->remove($competency);
        $this->om->flush();
        $this->abilityRepo->deleteOrphans();
    }

    /**
     * Creates a sub-competency.
     *
     * @param Competency $parent
     * @param Competency $child
     * @return Competency
     * @throws \LogicException if the competency already has abilities
     */
    public function createSubCompetency(Competency $parent, Competency $child)
    {
        if ($this->competencyAbilityRepo->countByCompetency($parent) > 0) {
            throw new \LogicException(
                "Cannot create sub-competency: competency {$parent->getId()}"
                . ' is already associated with abilities'
            );
        }

        $child->setParent($parent);
        $this->om->persist($child);
        $this->om->flush();

        return $child;
    }

    /**
     * Updates a competency.
     *
     * @param Competency $competency
     * @return Competency
     */
    public function updateCompetency(Competency $competency)
    {
        $this->om->flush();

        return $competency;
    }

    /**
     * Creates an ability and links it to a given competency.
     *
     * @param Competency    $parent
     * @param Ability       $ability
     * @param Level         $level
     * @return \HeVinci\CompetencyBundle\Entity\Ability
     * @throws \LogicException if the parent competency is not a leaf node
     */
    public function createAbility(Competency $parent, Ability $ability, Level $level)
    {
        if ($parent->getRight() - $parent->getLeft() > 1) {
            throw new \LogicException(
                "Cannot associate an ability with competency '{$parent->getName()}'"
                . ': competency must be a leaf node'
            );
        }

        $link = new CompetencyAbility();
        $link->setCompetency($parent);
        $link->setAbility($ability);
        $link->setLevel($level);

        $this->om->persist($ability);
        $this->om->persist($link);
        $this->om->flush();

        return $ability;
    }

    /**
     * Removes the association between a competency and an ability. If
     * the ability is not linked to any other competency, it is deleted
     * as well.
     *
     * @param Competency    $parent
     * @param Ability       $ability
     * @throws \Exception if ability is not linked to competency
     */
    public function removeAbility(Competency $parent, Ability $ability)
    {
        $linkCount = $this->competencyAbilityRepo->countByAbility($ability);
        $link = $this->competencyAbilityRepo->findOneByTerms($parent, $ability);
        $this->om->remove($link);

        if ($linkCount === 1) {
            $this->om->remove($ability);
        }

        $this->om->flush();
    }

    /**
     * Updates an ability.
     *
     * @param Competency    $parent
     * @param Ability       $ability
     * @param Level         $level
     * @return Ability
     * @throws \Exception if ability is not linked to competency
     */
    public function updateAbility(Competency $parent, Ability $ability, Level $level)
    {
        $link = $this->competencyAbilityRepo->findOneByTerms($parent, $ability);
        $link->setLevel($level);
        $this->om->flush();

        return $ability;
    }

    /**
     * Sets the level temporary attribute of an ability.
     *
     * @param Competency $parent
     * @param Ability $ability
     */
    public function loadAbility(Competency $parent, Ability $ability)
    {
        $link = $this->competencyAbilityRepo->findOneByTerms($parent, $ability);
        $ability->setLevel($link->getLevel());
    }

    /**
     * Returns the first five abilities whose name begins by a given string,
     * excluding the abilities linked to a particular competency.
     *
     * @see HeVinci\CompetencyBundle\Repository\AbilityRepository::findByFirstName
     *
     * @param Competency    $parent
     * @param string        $search
     * @return Ability[]
     */
    public function suggestAbilities(Competency $parent, $search)
    {
        return $this->abilityRepo->findFirstByName($search, $parent);
    }

    /**
     * Creates a link between a competency and an existing ability.
     *
     * @param Competency $parent
     * @param Ability $ability
     * @param Level $level
     * @return Ability
     * @throws \LogicException if a link already exists
     */
    public function linkAbilityToCompetency(Competency $parent, Ability $ability, Level $level)
    {
        $link = $this->competencyAbilityRepo->findOneBy([
            'competency' => $parent,
            'ability' => $ability
        ]);

        if ($link) {
            throw new \LogicException(
                "Ability {$ability->getId()} is already linked to competency {$parent->getId()}"
            );
        }

        $link = new CompetencyAbility();
        $link->setCompetency($parent);
        $link->setAbility($ability);
        $link->setLevel($level);

        $this->om->persist($link);
        $this->om->flush();

        return $ability;
    }
}
