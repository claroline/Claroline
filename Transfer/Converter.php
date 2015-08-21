<?php

namespace HeVinci\CompetencyBundle\Transfer;

use Claroline\CoreBundle\Persistence\ObjectManager;
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

    public function convertToEntity($frameworkData)
    {
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

        return $this->walkNodes($framework, $frameworkData, $scale);
    }

    public function convertToJson(Competency $framework)
    {
        $frameworkData = new \stdClass();
        $frameworkData->name = $framework->getName();
        $frameworkData->description = $framework->getDescription();

        $scaleData = new \stdClass();
        $scaleData->name = $framework->getScale()->getName();
        $scaleData->levels = [];

        foreach ($framework->getScale()->getLevels() as $level) {
            $scaleData->levels[] = $level->getName();
        }

        $frameworkData->scale = $scaleData;

        // reach children competencies...
    }

    private function walkNodes(Competency $parentCompetency, \stdClass $parentData, Scale $scale)
    {
        if (isset($parentData->competencies)) {
            foreach ($parentData->competencies as $competency) {
                $newCompetency = new Competency();
                $newCompetency->setName($competency->name);
                $newCompetency->setParent($parentCompetency);
                $this->walkNodes($newCompetency, $competency, $scale);
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
}
