<?php

namespace HeVinci\CompetencyBundle\Transfer\Validator;

use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("hevinci.competency.data_validator")
 */
class DataConstraintValidator
{
    /**
     * Validates a JSON decoded representation of a competency framework
     * against internal data constraints (i.e. consistency and uniqueness).
     * This method *requires* that the data has been validated against the
     * JSON schema.
     *
     * @param \stdClass $framework
     * @return string[] An array of error messages
     */
    public function validate(\stdClass $framework)
    {
        return $this->walkNodes($framework, [$framework->name], $framework->scale->levels);
    }

    private function walkNodes(\stdClass $parent, array $competencyNames, array $levels, array $errors = [])
    {
        if (isset($parent->competencies)) {
            foreach ($parent->competencies as $competency) {
                if (in_array($competency->name, $competencyNames)) {
                    $errors[] = "Duplicate competency name '{$competency->name}' within framework";
                } else {
                    $competencyNames[] = $competency->name;
                }

                $errors = array_merge($errors, $this->walkNodes($competency, $competencyNames, $levels));
            }
        } else {
            $abilityNames = [];

            foreach ($parent->abilities as $ability) {
                if (in_array($ability->name, $abilityNames)) {
                    $errors[] = "Ability '{$ability->name}' bound to competency '{$parent->name}' more than once";
                } else {
                    $abilityNames[] = $ability->name;
                }

                if (!in_array($ability->level, $levels)) {
                    $errors[] = "Level '{$ability->level}' of ability '{$ability->name}' not in framework scale";
                }
            }
        }

        return $errors;
    }
}
