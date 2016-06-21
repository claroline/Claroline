<?php

namespace HeVinci\CompetencyBundle\Transfer\Validator;

use JMS\DiExtraBundle\Annotation as DI;
use JVal\Validator;

/**
 * @DI\Service("hevinci.competency.json_validator")
 */
class JsonValidator
{
    /**
     * Validates JSON decoded data representing a competency framework
     * against the JSON schema.
     *
     * @param mixed $framework JSON decoded data
     *
     * @return array[] An array of JsonValidator errors
     */
    public function validate($framework)
    {
        $schemaDir = realpath(__DIR__.'/../../Resources/format');
        $schemaFile = "file://{$schemaDir}/framework.json";
        $schema = json_decode(file_get_contents($schemaFile));
        $validator = Validator::buildDefault();

        return $validator->validate($framework, $schema, $schemaFile);
    }
}
