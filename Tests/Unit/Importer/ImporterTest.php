<?php

namespace Icap\PortfolioBundle\Importer;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Transformer\XmlToArray;

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
      xmlns:categories="http://www.leapspecs.org/2A/categories">
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
            'leap2:version' => array(
                '$' => 'http://www.leapspecs.org/2010-07/2A/'
            ),
            'id' => array(
                '$' => '54be827257316'
            ),
            'title' => array(
                '$' => '54be8272572d8',
            ),
            'author' => array(
                'name' => array(
                    '$' => '54be827257361 54be82725739a'
                )
            ),
            'updated' => array(
                '$' => '2015-01-20T17:29:38+01:00'
            )
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

    public function testLeap2aImportMissingTitle()
    {
        $importer = new Importer();
        $user     = new User();
        $array    = array();

        $this->setExpectedException('Exception');

        $portfolio = $importer->import($array, Importer::IMPORT_FORMAT_LEAP2A, $user);
    }

    public function testImport()
    {
        $importer = new Importer();
        $user     = new User();
        $portfolioTitle = uniqid();
        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <title>$portfolioTitle</title>
</feed>
CONTENT;

        $portfolio = $importer->import($content, Importer::IMPORT_FORMAT_LEAP2A, $user);

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
      xmlns:categories="http://www.leapspecs.org/2A/categories">
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

    public function testRetrieveWidgetsWithOneSkillsWidget()
    {
        $content = <<<TEST
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <id>http://www.example.ac.uk/pfs/export_262144/</id>
    <title>A few small related items</title>
    <author>
        <name>Theophilus Thistledown</name>
    </author>
    <updated>2009-03-15T14:33:12Z</updated>

    <entry>
        <title>Mes compétences</title>
        <id>portfolio:skills/1</id>
        <updated>2010-10-07T22:30:23+02:00</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:selection"/>
        <category term="Abilities" scheme="categories:selection_type#"/>

        <link rel="leap2:has_part" href="portfolio:skill/1" leap2:display_order="1"/>
        <link rel="leap2:has_part" href="portfolio:skill/2" leap2:display_order="2"/>
    </entry>

    <entry>
        <title>skill</title>
        <id>portfolio:skill/1</id>
        <updated>2008-01-08T14:12:12Z</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:ability"/>

        <link rel="leap2:is_part_of" href="portfolio:skills/1" leap2:display_order="1"/>
    </entry>

    <entry>
        <title>skill 2</title>
        <id>portfolio:skill/2</id>
        <updated>2008-01-08T14:12:12Z</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:ability"/>

        <link rel="leap2:is_part_of" href="portfolio:skills/1" leap2:display_order="2"/>
    </entry>
</feed>
TEST;

        $transformer = new XmlToArray();
        $entries     = $transformer->transform($content)['entry'];
        $importer    = new Importer();

        $widgets = $importer->retrieveWidgets($entries);
        $this->assertEquals(1, count($widgets));
        $this->assertEquals(get_class($widgets[0]), 'Icap\PortfolioBundle\Entity\Widget\SkillsWidget');
        $this->assertEquals(2, count($widgets[0]->getSkills()));
    }

    public function testLeap2aImportPortfolioWithSkillWidget()
    {
        $importer = new Importer();

        $portfolioTitle = uniqid();

        $user = new User();
        $user
            ->setUsername(uniqid())
            ->setFirstName($firstname = uniqid())
            ->setLastName($lastname = uniqid());

        $skillsWidgetSkillId   = rand(0, PHP_INT_MAX);
        $skillsWidgetSkillName = uniqid();

        $skillsWidgetSkillId2   = rand(0, PHP_INT_MAX);
        $skillsWidgetSkillName2 = uniqid();

        $skillsWidgetId        = rand(0, PHP_INT_MAX);
        $skillsWidgetUpdatedAtText = (new \DateTime())->add(new \DateInterval('P2D'))->format(\DateTime::ATOM);
        $skillsWidgetLabel     = uniqid();

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <id>54bf7a6978100</id>
    <title>$portfolioTitle</title>
    <author>
        <name>$firstname $lastname</name>
    </author>
    <updated>2015-01-23T11:07:37+01:00</updated>

    <entry>
        <title>$skillsWidgetLabel</title>
        <id>portfolio:skills/$skillsWidgetId</id>
        <updated>$skillsWidgetUpdatedAtText</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:selection"/>
        <category term="Abilities" scheme="categories:selection_type#"/>

        <link rel="leap2:has_part" href="portfolio:skill/$skillsWidgetSkillId" leap2:display_order="1"/>
        <link rel="leap2:has_part" href="portfolio:skill/$skillsWidgetSkillId2" leap2:display_order="2"/>
    </entry>

    <entry>
        <title>$skillsWidgetSkillName</title>
        <id>portfolio:skill/$skillsWidgetSkillId</id>
        <updated>$skillsWidgetUpdatedAtText</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:ability"/>

        <link rel="leap2:is_part_of" href="portfolio:skills/$skillsWidgetId" leap2:display_order="1"/>
    </entry>
    <entry>
        <title>$skillsWidgetSkillName2</title>
        <id>portfolio:skill/$skillsWidgetSkillId2</id>
        <updated>$skillsWidgetUpdatedAtText</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:ability"/>

        <link rel="leap2:is_part_of" href="portfolio:skills/$skillsWidgetId" leap2:display_order="2"/>
    </entry>
</feed>
CONTENT;

        $importedPortfolio = $importer->import($content, 'leap2a', $user);

        $this->assertEquals('Icap\PortfolioBundle\Entity\Portfolio', get_class($importedPortfolio));

        $importedPortfolioTitleWidget = $importedPortfolio->getTitleWidget();
        $this->assertNotNull($importedPortfolioTitleWidget);
        $this->assertEquals($portfolioTitle, $importedPortfolioTitleWidget->getTitle());

        $this->assertEquals($importedPortfolio->getUser(), $user);

        $skillsWidgets = $importedPortfolio->getWidget('skills');
        $this->assertEquals(1, count($skillsWidgets));

        /** @var \Icap\PortfolioBundle\Entity\Widget\SkillsWidget $skillsWidget */
        $skillsWidget = $skillsWidgets[0];

        $this->assertEquals('Icap\PortfolioBundle\Entity\Widget\SkillsWidget', get_class($skillsWidget));
        $this->assertEquals($skillsWidgetLabel, $skillsWidget->getLabel());
        $this->assertEquals(2, count($skillsWidget->getSkills()));
    }

    public function testLeap2aImportPortfolioWithFormationWidget()
    {
        $importer = new Importer();

        $portfolioTitle = uniqid();

        $user = new User();
        $user
            ->setUsername(uniqid())
            ->setFirstName($firstname = uniqid())
            ->setLastName($lastname = uniqid());

        $formationsWidgetId        = rand(0, PHP_INT_MAX);
        $formationsWidgetStartedAt = new \DateTime();
        $formationsWidgetStartedAtText = (new \DateTime())->format(\DateTime::ATOM);
        $formationsWidgetUpdatedAtText = $formationsWidgetStartedAt->add(new \DateInterval('P2D'))->format(\DateTime::ATOM);
        $formationsWidgetEndedAtText   = $formationsWidgetStartedAt->add(new \DateInterval('P4D'))->format(\DateTime::ATOM);
        $formationsWidgetLabel     = uniqid();
        $formationsWidgetContent   = uniqid();

        $formationsWidgetResourceId = rand(0, PHP_INT_MAX);
        $formationsWidgetResourceUri = uniqid();

        $content = <<<CONTENT
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom"
      xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
      xmlns:leap2="http://terms.leapspecs.org/"
      xmlns:categories="http://www.leapspecs.org/2A/categories"
      xmlns:claroline="http://www.leapspecs.org/2A/categories">
    <leap2:version>http://www.leapspecs.org/2010-07/2A/</leap2:version>
    <id>54c793498714a</id>
    <title>$portfolioTitle</title>
    <author>
        <name>$firstname $lastname</name>
    </author>
    <updated>2015-01-29T14:31:53+01:00</updated>
    <entry>
        <title>$formationsWidgetLabel</title>
        <id>portfolio:formations/$formationsWidgetId</id>
        <updated>$formationsWidgetUpdatedAtText</updated>
        <content type="text">$formationsWidgetContent</content>
        <leap2:date leap2:point="start">$formationsWidgetStartedAtText</leap2:date>
        <leap2:date leap2:point="end">$formationsWidgetEndedAtText</leap2:date>
        <rdf:type rdf:resource="leap2:activity"/>
        <category term="Education" scheme="categories:life_area"/>
        <link rel="leap2:has_part" href="portfolio:resource/159163183" leap2:display_order="1"/>
    </entry>
    <entry>
        <title>54c7934987cb2</title>
        <id>portfolio:resource/$formationsWidgetResourceId</id>
        <uri>$formationsWidgetResourceUri</uri>
        <updated>$formationsWidgetUpdatedAtText</updated>
        <content></content>
        <rdf:type rdf:resource="leap2:resource"/>
        <category term="Web" scheme="categories:resource_type#"/>
        <link rel="leap2:is_part_of" href="portfolio:formations/$formationsWidgetId" leap2:display_order="1"/>
    </entry>
</feed>
CONTENT;

        $importedPortfolio = $importer->import($content, 'leap2a', $user);

        $this->assertEquals('Icap\PortfolioBundle\Entity\Portfolio', get_class($importedPortfolio));

        $importedPortfolioTitleWidget = $importedPortfolio->getTitleWidget();
        $this->assertNotNull($importedPortfolioTitleWidget);
        $this->assertEquals($portfolioTitle, $importedPortfolioTitleWidget->getTitle());

        $this->assertEquals($importedPortfolio->getUser(), $user);

        $formationsWidgets = $importedPortfolio->getWidget('formations');
        $this->assertEquals(1, count($formationsWidgets));

        /** @var \Icap\PortfolioBundle\Entity\Widget\FormationsWidget $formationsWidget */
        $formationsWidget = $formationsWidgets[0];

        $this->assertEquals('Icap\PortfolioBundle\Entity\Widget\FormationsWidget', get_class($formationsWidget));
        $this->assertEquals($formationsWidgetLabel, $formationsWidget->getLabel());

        $formationsWidgetResources = $formationsWidget->getResources();
        /** @var \Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource $formationsWidgetResource */
        $formationsWidgetResource = $formationsWidgetResources[0];

        $this->assertEquals(1, count($formationsWidgetResources));
        $this->assertEquals($formationsWidgetResourceUri, $formationsWidgetResource->getUri());
    }
}
