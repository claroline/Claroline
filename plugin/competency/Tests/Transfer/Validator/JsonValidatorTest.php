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
     *
     * @param string
     */
    public function testValidateWithValidData($dataFilename)
    {
        $file = __DIR__.'/../../../Resources/format/valid/'.$dataFilename;
        $data = json_decode(file_get_contents($file));
        $errors = $this->validator->validate($data);
        $this->assertEquals([], $errors);
    }

    /**
     * @dataProvider invalidDataProvider
     *
     * @param string
     * @param string
     * @param string
     */
    public function testValidateWithInvalidData($dataFilename, $expectedError, $expectedPath)
    {
        $file = __DIR__.'/../../../Resources/format/invalid/'.$dataFilename;
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
            ['full.json'],
        ];
    }

    public function invalidDataProvider()
    {
        return [
            ['no-name.json', 'property "name" is missing', ''],
            ['name-is-not-a-string.json', 'instance must be of type string', '/name'],
            ['name-is-empty.json', 'should be greater than or equal to 1 characters', '/name'],
            ['name-is-too-long.json', 'should be lesser than or equal to 500 characters', '/name'],
            ['no-description.json', 'property "description" is missing', ''],
            ['description-is-not-a-string.json', 'instance must be of type string', '/description'],
            ['description-is-empty.json', 'should be greater than or equal to 1 characters', '/description'],
            ['no-scale.json', 'property "scale" is missing', ''],
            ['scale-is-not-an-object.json', 'instance must be of type object', '/scale'],
            ['no-scale-name.json', 'property "name" is missing', '/scale'],
            ['scale-name-is-not-a-string.json', 'instance must be of type string', '/scale/name'],
            ['scale-name-is-empty.json', 'should be greater than or equal to 1 characters', '/scale/name'],
            ['scale-name-is-too-long.json', 'should be lesser than or equal to 255 characters', '/scale/name'],
            ['no-scale-levels.json', 'property "levels" is missing', '/scale'],
            ['scale-levels-is-not-an-array.json', 'instance must be of type array', '/scale/levels'],
            ['scale-levels-is-empty.json', 'number of items should be greater than or equal to 1', '/scale/levels'],
            ['scale-level-is-not-a-string.json', 'instance must be of type string', '/scale/levels/1'],
            ['scale-level-is-empty.json', 'should be greater than or equal to 1 characters', '/scale/levels/0'],
            ['scale-level-is-too-long.json', 'should be lesser than or equal to 255 characters', '/scale/levels/0'],
            ['duplicate-scale-level.json', 'elements must be unique', '/scale/levels'],
            ['competency-item-is-not-an-object.json', 'instance must be of type object', '/competencies/0'],
            ['competency-item-has-no-name.json', 'property "name" is missing', '/competencies/0'],
            ['duplicate-competency-item.json', 'elements must be unique', '/competencies/0/competencies'],
            ['ability-is-not-an-object.json', 'instance must be of type object', '/abilities/0'],
            ['no-ability-name.json', 'property "name" is missing', '/abilities/0'],
            ['ability-name-is-not-a-string.json', 'instance must be of type string', '/abilities/0/name'],
            ['ability-name-is-empty.json', 'should be greater than or equal to 1 characters', '/abilities/0/name'],
            ['ability-name-is-too-long.json', 'should be lesser than or equal to 500 characters', '/abilities/0/name'],
            ['no-ability-level.json', 'property "level" is missing', '/abilities/0'],
            ['ability-level-is-not-a-string.json', 'instance must be of type string', '/abilities/0/level'],
            ['ability-level-is-empty.json', 'should be greater than or equal to 1 characters', '/abilities/0/level'],
            ['ability-level-is-too-long.json', 'should be lesser than or equal to 255 characters', '/abilities/0/level'],
            ['duplicate-ability.json', 'elements must be unique', '/abilities'],
        ];
    }

    private function assertHasValidationError(array $errors, $expectedPath, $expectedMessage)
    {
        $this->assertGreaterThan(0, count($errors), 'At least one error was expected');

        foreach ($errors as $error) {
            if ($error['path'] === $expectedPath && $error['message'] === $expectedMessage) {
                return;
            }
        }

        $errorString = print_r($errors, true);
        $this->fail("Expected error was not found in the following:\n{$errorString})");
    }
}
