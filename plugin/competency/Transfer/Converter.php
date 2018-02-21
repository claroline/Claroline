<?php

namespace HeVinci\CompetencyBundle\Transfer;

use Claroline\AppBundle\Persistence\ObjectManager;
use HeVinci\CompetencyBundle\Entity\Ability;
use HeVinci\CompetencyBundle\Entity\Competency;
use HeVinci\CompetencyBundle\Entity\CompetencyAbility;
use HeVinci\CompetencyBundle\Entity\Level;
use HeVinci\CompetencyBundle\Entity\Scale;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.competency.transfer_converter")
 */
class Converter
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
     * Converts a JSON representation of a competency framework
     * into an entity graph (without persisting it).
     *
     * @param string $jsonFramework
     *
     * @return Competency
     */
    public function convertToEntity($jsonFramework)
    {
        $frameworkData = json_decode($jsonFramework);
        $scaleRepo = $this->om->getRepository('HeVinciCompetencyBundle:Scale');

        if (!($scale = $scaleRepo->findOneBy(['name' => $frameworkData->scale->name]))) {
            $scale = new Scale();
            $scale->setName($frameworkData->scale->name);

            for ($i = 0, $levels = $frameworkData->scale->levels, $max = count($levels); $i < $max; ++$i) {
                $level = new Level();
                $level->setName($levels[$i]);
                $level->setValue($i);
                $level->setScale($scale);
                $scale->addLevel($level);
            }
        }

        $framework = new Competency();
        $framework->setName($frameworkData->name);
        $framework->setDescription($frameworkData->description);
        $framework->setScale($scale);

        return $this->walkJsonNodes($frameworkData, $framework, $scale);
    }

    /**
     * Converts an array representation of a competency framework (as
     * returned by CompetencyManager#loadCompetency) into its JSON
     * representation.
     *
     * @param array $framework
     *
     * @return string
     */
    public function convertToJson(array $framework)
    {
        $scale = $this->om->getRepository('HeVinciCompetencyBundle:Competency')
            ->find($framework['id'])
            ->getScale();

        $frameworkData = new \stdClass();
        $frameworkData->name = $framework['name'];
        $frameworkData->description = $framework['description'];

        $frameworkData->scale = new \stdClass();
        $frameworkData->scale->name = $scale->getName();
        $frameworkData->scale->levels = $scale->getLevels()->map(function ($level) {
            return $level->getName();
        })->toArray();

        $this->walkArrayNodes($framework, $frameworkData);

        return json_encode($frameworkData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function walkJsonNodes(\stdClass $parentData, Competency $parentCompetency, Scale $scale)
    {
        if (isset($parentData->competencies)) {
            foreach ($parentData->competencies as $competency) {
                $newCompetency = new Competency();
                $newCompetency->setName($competency->name);
                $newCompetency->setParent($parentCompetency, true);
                $this->walkJsonNodes($competency, $newCompetency, $scale);
            }
        } else {
            foreach ($parentData->abilities as $ability) {
                $newAbility = new Ability();
                $newAbility->setName($ability->name);
                $level = null;

                foreach ($scale->getLevels() as $scaleLevel) {
                    if ($scaleLevel->getName() === $ability->level) {
                        $level = $scaleLevel;
                        break;
                    }
                }

                $link = new CompetencyAbility();
                $link->setCompetency($parentCompetency);
                $link->setAbility($newAbility);
                $link->setLevel($level);
            }
        }

        return $parentCompetency;
    }

    private function walkArrayNodes(array $parentCompetency, \stdClass $parentData)
    {
        if (isset($parentCompetency['__abilities'])) {
            $parentData->abilities = array_map(function ($ability) {
                $abilityData = new \stdClass();
                $abilityData->name = $ability['name'];
                $abilityData->level = $ability['levelName'];

                return $abilityData;
            }, $parentCompetency['__abilities']);
        } else {
            $parentData->competencies = array_map(function ($competency) {
                $competencyData = new \stdClass();
                $competencyData->name = $competency['name'];
                $this->walkArrayNodes($competency, $competencyData);

                return $competencyData;
            }, $parentCompetency['__children']);
        }

        return $parentData;
    }
}
