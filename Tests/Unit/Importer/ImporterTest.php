<?php

namespace Icap\PortfolioBundle\Importer;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;

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
        $user     = new User();

        $this->setExpectedException('InvalidArgumentException');

        $importer->import(uniqid(), uniqid(), $user);
    }

    public function testTransformContentLeap2a()
    {
        $importer = new Importer();
        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories"
      xmlns:claroline="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <id>54be827257316</id>
    <title>54be8272572d8</title>
    <author>
        <name>54be827257361 54be82725739a</name>
    </author>
    <updated>2015-01-20T17:29:38+01:00</updated>
</feed>
CONTENT;

        $expected = array(
            'id' => '54be827257316',
            'title' => '54be8272572d8',
            'author' => array(
                'name' => '54be827257361 54be82725739a'
            ),
            'updated' => '2015-01-20T17:29:38+01:00'
        );

        $this->assertEquals($expected, $importer->transformContent($content, 'leap2a'));
    }

    public function testTransformContentWrongFormat()
    {
        $importer = new Importer();
        $content = uniqid();

        $this->setExpectedException('InvalidArgumentException');

        $importer->transformContent($content, uniqid());
    }

    public function testArrayToPortfolioMissingTitle()
    {
        $importer = new Importer();
        $array    = array();

        $this->setExpectedException('Exception');

        $portfolio = $importer->arrayToPortfolio($array);
    }

    public function testArrayToPortfolio()
    {
        $importer = new Importer();
        $array    = array(
            'title' => $portfolioTitle = uniqid()
        );

        $portfolio = $importer->arrayToPortfolio($array);

        $this->assertEquals(get_class($portfolio), 'Icap\PortfolioBundle\Entity\Portfolio');

        $portfolioTitleWidget = $portfolio->getTitleWidget();
        $this->assertNotNull($portfolioTitleWidget);
        $this->assertEquals($portfolioTitle, $portfolioTitleWidget->getTitle());
    }

    public function testLeap2aImportEmptyPortfolio()
    {
        $importer = new Importer();

        $portfolioTitle = uniqid();

        $user = new User();
        $user
            ->setUsername(uniqid())
            ->setFirstName(uniqid())
            ->setLastName(uniqid());

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories"
      xmlns:claroline="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <id>54be827257316</id>
    <title>$portfolioTitle</title>
    <author>
        <name>54be827257361 54be82725739a</name>
    </author>
    <updated>2015-01-20T17:29:38+01:00</updated>
</feed>
CONTENT;

        $importedPortfolio = $importer->import($content, 'leap2a', $user);

        $this->assertEquals(get_class($importedPortfolio), 'Icap\PortfolioBundle\Entity\Portfolio');

        $importedPortfolioTitleWidget = $importedPortfolio->getTitleWidget();
        $this->assertNotNull($importedPortfolioTitleWidget);
        $this->assertEquals($portfolioTitle, $importedPortfolioTitleWidget->getTitle());

        $this->assertEquals($importedPortfolio->getUser(), $user);
    }
}
