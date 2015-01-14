<?php

namespace Icap\PortfolioBundle\Exporter;

use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\PortfolioBundle\Entity\Portfolio;

class ExporterTest extends MockeryTestCase
{
    public function testClassExists()
    {
        $exporter = new Exporter();

        $this->assertInstanceOf('\Icap\PortfolioBundle\Exporter\Exporter', $exporter);
    }

    public function testExportInWrongFormat()
    {
        $exporter = new Exporter();

        $expected = uniqid();
        $portfolio = new Portfolio();

        $this->setExpectedException('InvalidArgumentException');

        $exporter->export($portfolio, uniqid());
    }
}
