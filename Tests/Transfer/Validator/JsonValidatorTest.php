<?php

namespace HeVinci\CompetencyBundle\Transfer\Validator;

use HeVinci\CompetencyBundle\Util\UnitTestCase;

class JsonValidatorTest extends UnitTestCase
{
    private $validator;

    protected function setUp()
    {
        $this->validator = new JsonValidator();
    }

    /**
     * @dataProvider validDataProvider
     * @param string
     */
    public function testValidateWithValidData($dataFilename)
    {
        $file = __DIR__ . '/../../../Resources/format/valid/' . $dataFilename;
        $data = json_decode(file_get_contents($file));
        $errors = $this->validator->validate($data);
        $this->assertEquals([], $errors);
    }

    /**
     * @dataProvider invalidDataProvider
     * @param string
     * @param string
     * @param string
     */
    public function testValidateWithInvalidData($dataFilename, $expectedError, $expectedPath)
    {
        $file = __DIR__ . '/../../../Resources/format/invalid/' . $dataFilename;
        $data = json_decode(file_get_contents($file));
        $errors = $this->validator->validate($data);
        $this->assertHasValidationError($errors, $expectedPath, $expectedError);
    }

    public function validDataProvider()
    {
        return [
            ['minimal-1.json'],
            ['minimal-2.json'],
            ['intermediate-1.json'],
            ['intermediate-2.json'],
            ['full.json']
        ];
    }

    public function invalidDataProvider()
    {
        return [
            ['no-name.json', 'the property name is required', ''],
            ['name-is-not-a-string.json', 'array value found, but a string is required', 'name'],
            ['name-is-empty.json', 'must be at least 1 characters long', 'name'],
            ['name-is-too-long.json', 'must be at most 255 characters long', 'name'],
            ['no-description.json', 'the property description is required', ''],
            ['description-is-not-a-string.json', 'integer value found, but a string is required', 'description'],
            ['description-is-empty.json', 'must be at least 1 characters long', 'description'],
            ['no-scale.json', 'the property scale is required', ''],
            ['scale-is-not-an-object.json', 'string value found, but an object is required', 'scale'],
            ['no-scale-name.json', 'the property name is required', 'scale'],
            ['scale-name-is-not-a-string.json', 'integer value found, but a string is required', 'scale.name'],
            ['scale-name-is-empty.json', 'must be at least 1 characters long', 'scale.name'],
            ['scale-name-is-too-long.json', 'must be at most 255 characters long', 'scale.name'],
            ['no-scale-levels.json', 'the property levels is required', 'scale'],
            ['scale-levels-is-not-an-array.json', 'integer value found, but an array is required', 'scale.levels'],
            ['scale-levels-is-empty.json', 'There must be a minimum of 1 in the array', 'scale.levels'],
            ['scale-level-is-not-a-string.json', 'integer value found, but a string is required', 'scale.levels[1]'],
            ['scale-level-is-empty.json', 'must be at least 1 characters long', 'scale.levels[0]'],
            ['scale-level-is-too-long.json', 'must be at most 255 characters long', 'scale.levels[0]'],
            ['duplicate-scale-level.json', 'There are no duplicates allowed in the array', 'scale.levels'],
            ['competency-item-is-not-an-object.json', 'string value found, but an object is required', 'competencies[0]'],
            ['competency-item-has-no-name.json', 'the property name is required', 'competencies[0]'],
            ['duplicate-competency-item.json', 'There are no duplicates allowed in the array', 'competencies[0].competencies'],
            ['ability-is-not-an-object.json', 'integer value found, but an object is required', 'abilities[0]'],
            ['no-ability-name.json', 'the property name is required', 'abilities[0]'],
            ['ability-name-is-not-a-string.json', 'integer value found, but a string is required', 'abilities[0].name'],
            ['ability-name-is-empty.json', 'must be at least 1 characters long', 'abilities[0].name'],
            ['ability-name-is-too-long.json', 'must be at most 255 characters long', 'abilities[0].name'],
            ['no-ability-level.json', 'the property level is required', 'abilities[0]'],
            ['ability-level-is-not-a-string.json', 'integer value found, but a string is required', 'abilities[0].level'],
            ['ability-level-is-empty.json', 'must be at least 1 characters long', 'abilities[0].level'],
            ['ability-level-is-too-long.json', 'must be at most 255 characters long', 'abilities[0].level'],
            ['duplicate-ability.json', 'There are no duplicates allowed in the array', 'abilities']
        ];
    }

    private function assertHasValidationError(array $errors, $expectedProperty, $expectedMessage)
    {
        $this->assertGreaterThan(0, count($errors), 'At least one error was expected');

        foreach ($errors as $error) {
            if ($error['property'] === $expectedProperty && $error['message'] === $expectedMessage) {
                $this->assertTrue(true, 'Expected error was found');
                return;
            }
        }

        $errorString = print_r($errors, true);
        $this->assertTrue(false, "Expected error was not found in the following:\n{$errorString})");
    }
}
