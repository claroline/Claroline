<?php

namespace Icap\PortfolioBundle\Manager;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\PortfolioBundle\Importer\Leap2aImporter;

class ImportManagerTest extends MockeryTestCase
{
    public function testClassExists()
    {
        $importManager = new ImportManager();

        $this->assertInstanceOf('\Icap\PortfolioBundle\Manager\ImportManager', $importManager);
    }

    public function testGetAvailableImportFormats()
    {
        $importers = [
            new Leap2aImporter()
        ];
        $importManager = new ImportManager();

        $expected = array(
            'leap2a' => 'Leap2a'
        );

        $this->assertEquals($expected, $importManager->getAvailableFormats());
    }

    public function testGetAvailableImportFormatsWithTwoIdenticalImporter()
    {
        $importers = [
            new Leap2aImporter(),
            new Leap2aImporter()
        ];
        $importManager = new ImportManager();

        $expected = array(
            'leap2a' => 'Leap2a'
        );

        $this->assertEquals($expected, $importManager->getAvailableFormats());
    }

    public function testImportWithWrongFormat()
    {
        $importManager = new ImportManager();

        $expected = array(
            'leap2a' => 'Leap2a'
        );

        $expectedFormat = uniqid();

        $this->setExpectedException('Exception', "No importer for the '$expectedFormat' format.");

        $portfolio = $importManager->simulateImport(uniqid(), new User(), $expectedFormat);
    }
}
