<?php

namespace Icap\PortfolioBundle\Exporter;

use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use FOS\JsRoutingBundle\Tests\Extractor\ExposedRoutesExtractorTest;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Symfony\Bridge\Twig\TwigEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Twig_Environment;
use Twig_Loader_Array;
use Twig_Loader_Filesystem;

class ExporterTest extends MockeryTestCase
{
    private $twigEngine;

    protected function setUp()
    {
        parent::setUp();

        $templateLoader = new Twig_Loader_Array(array(
            'IcapPortfolioBundle:Exporter:leap2a.xml.twig' => file_get_contents(__DIR__ . '/../../../Resources/views/export/leap2a.xml.twig'),
        ));

        $twigEnvironment  = new Twig_Environment($templateLoader);
        $this->twigEngine = new TwigEngine($twigEnvironment, new TemplateNameParser());
    }

    /**
     * @param string $firstname
     * @param string $lastname
     *
     * @return User
     */
    public function createUser($firstname, $lastname)
    {
        $username = uniqid();

        $user = new User();
        $user
            ->setFirstName($firstname)
            ->setLastName($lastname);

        return $user;
    }

    public function testClassExists()
    {
        $exporter = new Exporter($this->twigEngine);

        $this->assertInstanceOf('\Icap\PortfolioBundle\Exporter\Exporter', $exporter);
    }

    public function testExportInWrongFormat()
    {
        $exporter = new Exporter($this->twigEngine);

        $expected = uniqid();
        $portfolio = new Portfolio();

        $this->setExpectedException('InvalidArgumentException');

        $exporter->export($portfolio, uniqid());
    }

    public function testExportEmptyPortfolio()
    {
        $exporter = new Exporter($this->twigEngine);

        /** @var TitleWidget $titleWidget */
        $titleWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\TitleWidget[getUpdatedAt]');
        $titleWidget->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());
        $titleWidget
            ->setTitle($portfolioTitle = uniqid())
            ->setSlug($portfolioSlug = uniqid());

        $portfolio = new Portfolio();
        $portfolio
            ->setUser($this->createUser($firstname = uniqid(), $lastname = uniqid()))
            ->setWidgets(array($titleWidget));

        $actual = $exporter->export($portfolio, 'leap2a');
        $portfolioLastUpdateDate = $titleWidget->getUpdatedAt()->format(\DateTime::ATOM);
        $expected = <<<EXPORT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories"
      xmlns:claroline="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <id>$portfolioSlug</id>
    <title>$portfolioTitle</title>
    <author>
        <name>$firstname $lastname</name>
    </author>
    <updated>$portfolioLastUpdateDate</updated>
</feed>
EXPORT;

        $this->assertEquals($expected, $actual);
    }
}
