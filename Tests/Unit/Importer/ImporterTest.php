<?php

namespace Icap\PortfolioBundle\Importer;

use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\PortfolioBundle\Entity\Portfolio;

class ImporterTest extends MockeryTestCase
{
    public function testClassExists()
    {
        $importer = new Importer();

        $this->assertInstanceOf('\Icap\PortfolioBundle\Importer\Importer', $importer);
    }

    public function testImportInWrongFormat()
    {
        $importer = new Importer();

        $this->setExpectedException('InvalidArgumentException');

        $importer->import(uniqid(), uniqid());
    }
}
