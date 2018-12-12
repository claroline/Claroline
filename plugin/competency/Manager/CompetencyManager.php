<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
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
    private $levelRepo;

    /**
     * @DI\InjectParams({
     *     "om"         = @DI\Inject("claroline.persistence.object_manager"),
     *     "translator" = @DI\Inject("translator")
     * })
     *
     * @param ObjectManager       $om
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ObjectManager $om,
        TranslatorInterface $translator
    ) {
        $this->om = $om;
        $this->competencyRepo = $om->getRepository('HeVinciCompetencyBundle:Competency');
        $this->scaleRepo = $om->getRepository('HeVinciCompetencyBundle:Scale');
        $this->abilityRepo = $om->getRepository('HeVinciCompetencyBundle:Ability');
        $this->competencyAbilityRepo = $om->getRepository('HeVinciCompetencyBundle:CompetencyAbility');
        $this->translator = $translator;
        $this->levelRepo = $om->getRepository('HeVinciCompetencyBundle:Level');
    }

    /**
     * Returns whether there are scales registered in the database.
     *
     * @return bool
     */
    public function hasScales()
    {
        return $this->om->count(Scale::class) > 0;
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

    /****************************************************************************************************************/

    /**
     * Returns the list of registered frameworks.
     *
     * @param bool $shortArrays Whether full entities or minimal arrays should be returned
     *
     * @return array
     */
    public function listFrameworks($shortArrays = false)
    {
        $frameworks = $this->competencyRepo->findBy(['parent' => null]);

        return !$shortArrays ?
            $frameworks :
            array_map(function ($framework) {
                return [
                    'id' => $framework->getId(),
                    'name' => $framework->getName(),
                    'description' => $framework->getDescription(),
                ];
            }, $frameworks);
    }

    /**
     * Returns a full array representation of a competency tree. Children
     * competencies and linked abilities are respectively stored under the
     * "__children" and "__abilities" keys of their corresponding competency
     * array.
     *
     * @param Competency $competency    The competency to be loaded
     * @param bool       $loadAbilities Whether linked abilities should be included
     *
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

        return $this->walkCollection($competencies, function ($collection) use ($abilitiesByCompetency) {
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
     *
     * @throws \LogicException
     */
    public function ensureIsRoot(Competency $competency)
    {
        if ($competency->getRoot() !== $competency->getId()) {
            throw new \LogicException('Framework edition must be done on the root competency');
        }
    }

    /**
     * Sets the level temporary attribute of an ability.
     *
     * @param Competency $parent
     * @param Ability    $ability
     */
    public function loadAbility(Competency $parent, Ability $ability)
    {
        $link = $this->competencyAbilityRepo->findOneByTerms($parent, $ability);
        $ability->setLevel($link->getLevel());
    }

    /**
     * Utility method. Walks a collection recursively, applying a callback on
     * each element.
     *
     * Unlike array_walk_recursive :
     * - the result is a *copy* of the original collection
     * - all the nodes are visited, not only the leafs
     *
     * @param mixed    $collection
     * @param callable $callback
     *
     * @return mixed
     */
    public function walkCollection($collection, \Closure $callback)
    {
        if (is_array($collection)) {
            $result = [];

            foreach ($collection as $key => $item) {
                $result[$key] = $this->walkCollection($item, $callback);
            }

            return $callback($result);
        }

        return $collection;
    }

    public function getCompetencyById($competencyId)
    {
        return $this->competencyRepo->findOneById($competencyId);
    }

    public function getCompetencyAbilityLinksByCompetencyAndLevel(Competency $competency, Level $level)
    {
        return $this->competencyAbilityRepo->findBy(['competency' => $competency, 'level' => $level]);
    }

    public function getLevelByScaleAndValue(Scale $scale, $value)
    {
        return $this->levelRepo->findOneBy(['scale' => $scale, 'value' => $value]);
    }
}
