<?php

namespace HeVinci\CompetencyBundle\Manager;

use Claroline\AppBundle\Persistence\ObjectManager;
use Doctrine\Common\Collections\ArrayCollection;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Scale;
use HeVinci\CompetencyBundle\Transfer\Converter;
use Symfony\Component\Translation\TranslatorInterface;

class CompetencyManager
{
    private $converter;
    private $om;
    private $translator;

    private $abilityRepo;
    private $competencyAbilityRepo;
    private $competencyRepo;
    private $levelRepo;
    private $scaleRepo;

    public function __construct(
        Converter $converter,
        ObjectManager $om,
        TranslatorInterface $translator
    ) {
        $this->converter = $converter;
        $this->om = $om;
        $this->translator = $translator;

        $this->abilityRepo = $om->getRepository(Ability::class);
        $this->competencyAbilityRepo = $om->getRepository(CompetencyAbility::class);
        $this->competencyRepo = $om->getRepository(Competency::class);
        $this->levelRepo = $om->getRepository(Level::class);
        $this->scaleRepo = $om->getRepository(Scale::class);
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

    /**
     * Ensures a competency is the root of the framework.
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
     * Returns the JSON representation of a competency framework.
     *
     * @return string
     */
    public function exportFramework(Competency $framework)
    {
        $loaded = $this->loadCompetency($framework);

        return $this->converter->convertToJson($loaded);
    }

    /**
     * Imports a competency framework described in a JSON string.
     *
     * @param string $frameworkData
     *
     * @return Competency
     */
    public function importFramework($frameworkData)
    {
        $framework = $this->converter->convertToEntity($frameworkData);

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
     * Utility method. Walks a collection recursively, applying a callback on
     * each element.
     *
     * Unlike array_walk_recursive :
     * - the result is a *copy* of the original collection
     * - all the nodes are visited, not only the leafs
     *
     * @param mixed $collection
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

    /**
     * Associates a competency to several resource nodes.
     *
     * @return string
     */
    public function associateCompetencyToResources(Competency $competency, array $nodes)
    {
        $associatedNodes = [];

        foreach ($nodes as $node) {
            if (!$competency->isLinkedToResource($node)) {
                $competency->linkResource($node);
                $associatedNodes[] = $node;
            }
        }
        $this->om->persist($competency);
        $this->om->flush();

        return $associatedNodes;
    }

    /**
     * Dissociates a competency from several resource nodes.
     *
     * @return string
     */
    public function dissociateCompetencyFromResources(Competency $competency, array $nodes)
    {
        foreach ($nodes as $node) {
            $competency->removeResource($node);
        }
        $this->om->persist($competency);
        $this->om->flush();
    }

    /**
     * Associates an ability to several resource nodes.
     *
     * @return string
     */
    public function associateAbilityToResources(Ability $ability, array $nodes)
    {
        $associatedNodes = [];

        foreach ($nodes as $node) {
            if (!$ability->isLinkedToResource($node)) {
                $ability->linkResource($node);
                $associatedNodes[] = $node;
            }
        }
        $this->om->persist($ability);
        $this->om->flush();

        return $associatedNodes;
    }

    /**
     * Dissociates an ability from several resource nodes.
     *
     * @return string
     */
    public function dissociateAbilityFromResources(Ability $ability, array $nodes)
    {
        foreach ($nodes as $node) {
            $ability->removeResource($node);
        }
        $this->om->persist($ability);
        $this->om->flush();
    }

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
     * Sets the level temporary attribute of an ability.
     */
    public function loadAbility(Competency $parent, Ability $ability)
    {
        $link = $this->competencyAbilityRepo->findOneByTerms($parent, $ability);
        $ability->setLevel($link->getLevel());
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
