<?php

namespace HeVinci\CompetencyBundle\Transfer\Validator;

use Claroline\AppBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.competency.data_conflict_validator")
 */
class DataConflictValidator
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
     * Validates a JSON decoded representation of a competency framework
     * against already existing data (i.e. uniqueness and correctness in
     * references). This method *requires* that the data has been
     * validated against the JSON schema.
     *
     * @param \stdClass $framework
     *
     * @return string[] An array of error messages
     */
    public function validate(\stdClass $framework)
    {
        $competencyRepo = $this->om->getRepository('HeVinciCompetencyBundle:Competency');
        $scaleRepo = $this->om->getRepository('HeVinciCompetencyBundle:Scale');
        $errors = [];

        if ($competencyRepo->findOneBy(['name' => $framework->name])) {
            $errors[] = "There's already a framework named '{$framework->name}'";
        }

        if ($existingScale = $scaleRepo->findOneBy(['name' => $framework->scale->name])) {
            $existingLevels = $existingScale->getLevels()->map(function ($level) {
                return $level->getName();
            })->toArray();

            if ($existingLevels !== $framework->scale->levels) {
                $errors[] = "Framework scale levels don't match those of already existing scale '{$existingScale->getName()}'";
            }
        }

        return $errors;
    }
}
