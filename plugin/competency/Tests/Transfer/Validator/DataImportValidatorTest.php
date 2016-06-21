<?php

namespace HeVinci\CompetencyBundle\Transfer\Validator;

use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class DataImportValidatorTest extends RepositoryTestCase
{
    private $validator;

    protected function setUp()
    {
        parent::setUp();
        $this->validator = $this->client->getContainer()
            ->get('hevinci.competency.data_conflict_validator');
    }

    public function testValidateChecksForFrameworkUniqueName()
    {
        $this->persistCompetency('Civil service competency framework');
        $this->om->flush();

        $errors = $this->validator->validate($this->getValidData('minimal-1.json'));
        $this->assertContains(
            "There's already a framework named 'Civil service competency framework'",
            $errors,
            "Validation errors:\n".print_r($errors, true)
        );
    }

    /**
     * @dataProvider scaleLevelsProvider
     *
     * @param array $scaleLevels
     */
    public function testValidateChecksForScaleReferenceCorrectness(array $scaleLevels)
    {
        $scale = $this->persistScale('Civil service levels');

        foreach ($scaleLevels as $level) {
            $this->persistLevel($level, $scale);
        }

        $this->om->flush();

        $errors = $this->validator->validate($this->getValidData('minimal-1.json'));
        $printedErrors = "Validation errors:\n".print_r($errors, true);

        $this->assertContains("Framework scale levels don't match those of already existing scale 'Civil service levels'", $errors, $printedErrors);
    }

    public function testValidateWithValidData()
    {
        $errors = $this->validator->validate($this->getValidData('full.json'));
        $this->assertEquals([], $errors);
    }

    public function scaleLevelsProvider()
    {
        return [
            [['Level 1', 'Level 2']],
            [['Level 1', 'Level 2', 'Level 3', 'Level 4']],
            [['Level 2', 'Level 1', 'Level 3']],
            [['A', 'Level 2', 'Level 3']],
        ];
    }

    private function getValidData($fileName)
    {
        $file = __DIR__.'/../../../Resources/format/valid/'.$fileName;

        return json_decode(file_get_contents($file));
    }
}
