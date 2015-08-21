<?php

namespace HeVinci\CompetencyBundle\Transfer;

use HeVinci\CompetencyBundle\Util\RepositoryTestCase;

class ConverterTest extends RepositoryTestCase
{
    private $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->converter = $this->client->getContainer()
            ->get('hevinci.competency.transfer_converter');
    }

    public function testConvertToEntity()
    {
        $file = __DIR__ . '/../../Resources/format/valid/minimal-1.json';
        $data = json_decode(file_get_contents($file));

        $framework = $this->converter->convertToEntity($data);
        $scale = $framework->getScale();

        $this->assertInstanceOf('HeVinci\CompetencyBundle\Entity\Competency', $framework);
        $this->assertEquals('Civil service competency framework', $framework->getName());
        $this->assertEquals('Competency framework to support the Civil Service Reform Plan and the new performance management system.', $framework->getDescription());
        $this->assertInstanceOf('HeVinci\CompetencyBundle\Entity\Scale', $framework->getScale());
        $this->assertEquals('Civil service levels', $framework->getScale()->getName());
        $this->assertEquals(3, $scale->getLevels()->count());
        $this->assertContainsOnlyInstancesOf('HeVinci\CompetencyBundle\Entity\Level', $scale->getLevels());
        $this->assertEquals('Level 1', $scale->getLevels()[0]->getName());
        $this->assertEquals('Level 2', $scale->getLevels()[1]->getName());
        $this->assertEquals('Level 3', $scale->getLevels()[2]->getName());
        $this->assertEquals(0, $scale->getLevels()[0]->getValue());
        $this->assertEquals(1, $scale->getLevels()[1]->getValue());
        $this->assertEquals(2, $scale->getLevels()[2]->getValue());
    }
}
