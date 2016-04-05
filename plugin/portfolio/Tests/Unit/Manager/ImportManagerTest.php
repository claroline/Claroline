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
        $importManager = new ImportManager($importers);

        $expected = array(
            'leap2a' => 'Leap2a'
        );

        $this->assertEquals($expected, $importManager->getAvailableFormats());

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
        $importManager = new ImportManager($importers);

        $expected = array(
            'leap2a' => 'Leap2a'
        );

        $this->assertEquals($expected, $importManager->getAvailableFormats());
    }

    public function testImportWithWrongFormat()
    {
        $importManager = new ImportManager();

        $expectedFormat = uniqid();

        $this->setExpectedException('Exception', "No importer for the '$expectedFormat' format.");

        $portfolio = $importManager->simulateImport(uniqid(), new User(), $expectedFormat);
    }

    public function testDoImportWithoutEntityManager()
    {
        $importManager = new ImportManager();
        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <title>title</title>
</feed>
CONTENT;

        $this->setExpectedException('Exception', 'No entity manager, you can only simulate an import.');

        $portfolio = $importManager->doImport($content, new User(), 'leap2a');
    }
}
