<?php

namespace HeVinci\CompetencyBundle\Transfer\Validator;

use HeVinci\CompetencyBundle\Util\UnitTestCase;

class DataConstraintValidatorTest extends UnitTestCase
{
    private $validator;

    protected function setUp()
    {
        $this->validator = new DataConstraintValidator();
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
     */
    public function testValidateWithInvalidData($dataFilename, $expectedError)
    {
        $dataDir = realpath(__DIR__.'/../../../Resources/format/invalid/additional');
        $file = "{$dataDir}/{$dataFilename}";
        $data = json_decode(file_get_contents($file));
        $errors = $this->validator->validate($data);
        $this->assertContains($expectedError, $errors, "Validation errors:\n".print_r($errors, true));
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
            ['duplicate-competency-name.json', "Duplicate competency name 'Setting direction' within framework"],
            ['duplicate-competency-name.json', "Duplicate competency name 'Civil service competency framework' within framework"],
            ['duplicate-competency-ability.json', "Ability 'Gathering information from a range of relevant sources inside and outside their Department to inform own work' bound to competency 'Seeing the big picture' more than once"],
            ['ability-level-is-not-in-scale.json', "Level 'A2' of ability 'Keeping up to date with a broad set of issues relating to the work of the Department' not in framework scale"],
        ];
    }
}
