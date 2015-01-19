<?php

namespace Icap\PortfolioBundle\Exporter;

use Claroline\CoreBundle\Entity\Badge\BadgeTranslation;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Rule\Entity\Rule;
use Claroline\CoreBundle\Entity\Badge\Badge;
use Claroline\CoreBundle\Entity\Badge\BadgeRule;
use Claroline\CoreBundle\Entity\Log\Log;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Testing\MockeryTestCase;
use FOS\JsRoutingBundle\Tests\Extractor\ExposedRoutesExtractorTest;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\BadgesWidgetBadge;
use Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource;
use Icap\PortfolioBundle\Entity\Widget\SkillsWidget;
use Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill;
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
            'IcapPortfolioBundle:Exporter:export.leap2a.twig' => file_get_contents(__DIR__ . '/../../../Resources/views/export/export.leap2a.twig'),
            'IcapPortfolioBundle:export\leap2a:badges.leap2a.twig' => file_get_contents(__DIR__ . '/../../../Resources/views/export/leap2a/badges.leap2a.twig'),
            'IcapPortfolioBundle:export\leap2a:skills.leap2a.twig' => file_get_contents(__DIR__ . '/../../../Resources/views/export/leap2a/skills.leap2a.twig'),
            'IcapPortfolioBundle:export\leap2a:text.leap2a.twig' => file_get_contents(__DIR__ . '/../../../Resources/views/export/leap2a/text.leap2a.twig'),
            'IcapPortfolioBundle:export\leap2a:userInformation.leap2a.twig' => file_get_contents(__DIR__ . '/../../../Resources/views/export/leap2a/userInformation.leap2a.twig'),
            'IcapPortfolioBundle:export\leap2a:formations.leap2a.twig' => file_get_contents(__DIR__ . '/../../../Resources/views/export/leap2a/formations.leap2a.twig')
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

    public function testLeap2aExportEmptyPortfolio()
    {
        $exporter = new Exporter($this->twigEngine);

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
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

    public function testLeap2aExportPortfolioWithOneBadge()
    {
        $exporter = new Exporter($this->twigEngine);

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\TitleWidget[getUpdatedAt]');
        $titleWidget->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());
        $titleWidget
            ->setTitle($portfolioTitle = uniqid())
            ->setSlug($portfolioSlug = uniqid());

        /** @var \Icap\PortfolioBundle\Entity\Widget\BadgesWidget $badgesWidget */
        $badgesWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\BadgesWidget[getId, getUpdatedAt]');
        $badgesWidget->shouldReceive('getId')->andReturn($badgesWidgetId = rand(0, PHP_INT_MAX));
        $badgesWidget->shouldReceive('getUpdatedAt')->andReturn($badgesWidgetUpdatedAt = (new \DateTime())->add(new \DateInterval('P2D')));

        $badgeTranslation = new BadgeTranslation();
        $badgeTranslation
            ->setSlug($badgeSlug = uniqid())
            ->setLocale('fr')
            ->setName($badgeName = uniqid())
            ->setDescription($badgeDescription = uniqid())
            ->setCriteria($badgeCriteria = uniqid());

        $badge = new Badge();
        $badge
            ->addTranslation($badgeTranslation)
            ->setLocale('fr');

        $badgesWidgetBadge = new BadgesWidgetBadge();
        $badgesWidgetBadge
            ->setBadge($badge)
            ->setId($badgesWidgetBadgeId = rand(0, PHP_INT_MAX));

        $badgesWidget
            ->setBadges(array($badgesWidgetBadge))
            ->setLabel($badgesWidgetLabel = uniqid());

        $portfolio = new Portfolio();
        $portfolio
            ->setUser($this->createUser($firstname = uniqid(), $lastname = uniqid()))
            ->setWidgets(array($titleWidget, $badgesWidget));

        $actual = $exporter->export($portfolio, 'leap2a');
        $portfolioLastUpdateDate = $badgesWidgetUpdatedAt->format(\DateTime::ATOM);

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

    <entry>
        <title>$badgesWidgetLabel</title>
        <id>portfolio:badges/$badgesWidgetId</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:selection"/>
        <category term="Grouping" scheme="categories:selection_type#"/>

        <link rel="leap2:has_part" href="portfolio:badge/$badgesWidgetBadgeId" leap2:display_order="1"/>
    </entry>

    <entry>
        <title>$badgeName</title>
        <id>portfolio:badge/$badgesWidgetBadgeId</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content type="html">$badgeDescription</content>

        <rdf:type rdf:resource="leap2:achievement"/>

        <link rel="leap2:is_part_of" href="portfolio:badges/$badgesWidgetId" leap2:display_order="1"/>
    </entry>
</feed>
EXPORT;

        $this->assertEquals($expected, $actual);
    }

    public function testLeap2aExportPortfolioWithTwoBadge()
    {
        $exporter = new Exporter($this->twigEngine);

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\TitleWidget[getUpdatedAt]');
        $titleWidget->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());
        $titleWidget
            ->setTitle($portfolioTitle = uniqid())
            ->setSlug($portfolioSlug = uniqid());

        /** @var \Icap\PortfolioBundle\Entity\Widget\BadgesWidget $badgesWidget */
        $badgesWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\BadgesWidget[getId, getUpdatedAt]');
        $badgesWidget->shouldReceive('getId')->andReturn($badgesWidgetId = rand(0, PHP_INT_MAX));
        $badgesWidget->shouldReceive('getUpdatedAt')->andReturn($badgesWidgetUpdatedAt = (new \DateTime())->add(new \DateInterval('P2D')));

        $badgeTranslation = new BadgeTranslation();
        $badgeTranslation
            ->setSlug($badgeSlug = uniqid())
            ->setLocale('fr')
            ->setName($badgeName = uniqid())
            ->setDescription($badgeDescription = uniqid())
            ->setCriteria($badgeCriteria = uniqid());

        $badge = new Badge();
        $badge
            ->addTranslation($badgeTranslation)
            ->setLocale('fr');

        $badgesWidgetBadge = new BadgesWidgetBadge();
        $badgesWidgetBadge
            ->setBadge($badge)
            ->setId($badgesWidgetBadgeId = rand(0, PHP_INT_MAX));

        $badgesWidgetBadge2 = new BadgesWidgetBadge();
        $badgesWidgetBadge2
            ->setBadge($badge)
            ->setId($badgesWidgetBadge2Id = rand(0, PHP_INT_MAX));

        $badgesWidget
            ->setBadges(array($badgesWidgetBadge, $badgesWidgetBadge2))
            ->setLabel($badgesWidgetLabel = uniqid());

        $portfolio = new Portfolio();
        $portfolio
            ->setUser($this->createUser($firstname = uniqid(), $lastname = uniqid()))
            ->setWidgets(array($titleWidget, $badgesWidget));

        $actual = $exporter->export($portfolio, 'leap2a');
        $portfolioLastUpdateDate = $badgesWidgetUpdatedAt->format(\DateTime::ATOM);

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

    <entry>
        <title>$badgesWidgetLabel</title>
        <id>portfolio:badges/$badgesWidgetId</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:selection"/>
        <category term="Grouping" scheme="categories:selection_type#"/>

        <link rel="leap2:has_part" href="portfolio:badge/$badgesWidgetBadgeId" leap2:display_order="1"/>
        <link rel="leap2:has_part" href="portfolio:badge/$badgesWidgetBadge2Id" leap2:display_order="2"/>
    </entry>

    <entry>
        <title>$badgeName</title>
        <id>portfolio:badge/$badgesWidgetBadgeId</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content type="html">$badgeDescription</content>

        <rdf:type rdf:resource="leap2:achievement"/>

        <link rel="leap2:is_part_of" href="portfolio:badges/$badgesWidgetId" leap2:display_order="1"/>
    </entry>
    <entry>
        <title>$badgeName</title>
        <id>portfolio:badge/$badgesWidgetBadge2Id</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content type="html">$badgeDescription</content>

        <rdf:type rdf:resource="leap2:achievement"/>

        <link rel="leap2:is_part_of" href="portfolio:badges/$badgesWidgetId" leap2:display_order="2"/>
    </entry>
</feed>
EXPORT;

        $this->assertEquals($expected, $actual);
    }

    public function testLeap2aExportPortfolioWithTwoSkills()
    {
        $exporter = new Exporter($this->twigEngine);

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\TitleWidget[getUpdatedAt]');
        $titleWidget->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());
        $titleWidget
            ->setTitle($portfolioTitle = uniqid())
            ->setSlug($portfolioSlug = uniqid());

        $skillsWidgetSkill = $this->mock('Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill[getId]');
        $skillsWidgetSkill->shouldReceive('getId')->andReturn($skillsWidgetSkillId = rand(0, PHP_INT_MAX));
        $skillsWidgetSkill
            ->setName($skillsWidgetSkillName = uniqid());

        $skillsWidgetSkill2 = $this->mock('Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill[getId]');
        $skillsWidgetSkill2->shouldReceive('getId')->andReturn($skillsWidgetSkillId2 = rand(0, PHP_INT_MAX));
        $skillsWidgetSkill2
            ->setName($skillsWidgetSkillName2 = uniqid());

        $skillsWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\SkillsWidget[getId, getUpdatedAt]');
        $skillsWidget->shouldReceive('getId')->andReturn($skillsWidgetId = rand(0, PHP_INT_MAX));
        $skillsWidget->shouldReceive('getUpdatedAt')->andReturn($skillsWidgetUpdatedAt = (new \DateTime())->add(new \DateInterval('P2D')));
        $skillsWidget
            ->setSkills(array($skillsWidgetSkill, $skillsWidgetSkill2))
            ->setLabel($skillsWidgetLabel = uniqid());

        $portfolio = new Portfolio();
        $portfolio
            ->setUser($this->createUser($firstname = uniqid(), $lastname = uniqid()))
            ->setWidgets(array($titleWidget, $skillsWidget));

        $actual = $exporter->export($portfolio, 'leap2a');
        $portfolioLastUpdateDate = $skillsWidgetUpdatedAt->format(\DateTime::ATOM);
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

    <entry>
        <title>$skillsWidgetLabel</title>
        <id>portfolio:skills/$skillsWidgetId</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:selection"/>
        <category term="Abilities" scheme="categories:selection_type#"/>

        <link rel="leap2:has_part" href="portfolio:skill/$skillsWidgetSkillId" leap2:display_order="1"/>
        <link rel="leap2:has_part" href="portfolio:skill/$skillsWidgetSkillId2" leap2:display_order="2"/>
    </entry>

    <entry>
        <title>$skillsWidgetSkillName</title>
        <id>portfolio:skill/$skillsWidgetSkillId</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:ability"/>

        <link rel="leap2:is_part_of" href="portfolio:skills/$skillsWidgetId" leap2:display_order="1"/>
    </entry>
    <entry>
        <title>$skillsWidgetSkillName2</title>
        <id>portfolio:skill/$skillsWidgetSkillId2</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:ability"/>

        <link rel="leap2:is_part_of" href="portfolio:skills/$skillsWidgetId" leap2:display_order="2"/>
    </entry>
</feed>
EXPORT;

        $this->assertEquals($expected, $actual);
    }

    public function testLeap2aExportPortfolioWithTwoWidgetOfOneSkills()
    {
        $exporter = new Exporter($this->twigEngine);

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\TitleWidget[getUpdatedAt]');
        $titleWidget->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());
        $titleWidget
            ->setTitle($portfolioTitle = uniqid())
            ->setSlug($portfolioSlug = uniqid());

        $skillsWidgetSkill = $this->mock('Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill[getId]');
        $skillsWidgetSkill->shouldReceive('getId')->andReturn($skillsWidgetSkillId = rand(0, PHP_INT_MAX));
        $skillsWidgetSkill
            ->setName($skillsWidgetSkillName = uniqid());

        $skillsWidgetSkill2 = $this->mock('Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill[getId]');
        $skillsWidgetSkill2->shouldReceive('getId')->andReturn($skillsWidgetSkillId2 = rand(0, PHP_INT_MAX));
        $skillsWidgetSkill2
            ->setName($skillsWidgetSkillName2 = uniqid());

        $skillsWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\SkillsWidget[getId, getUpdatedAt]');
        $skillsWidget->shouldReceive('getId')->andReturn($skillsWidgetId = rand(0, PHP_INT_MAX));
        $skillsWidget->shouldReceive('getUpdatedAt')->andReturn($skillsWidgetUpdatedAt = (new \DateTime())->add(new \DateInterval('P2D')));
        $skillsWidget
            ->setSkills(array($skillsWidgetSkill))
            ->setLabel($skillsWidgetLabel = uniqid());

        $skillsWidget2 = $this->mock('Icap\PortfolioBundle\Entity\Widget\SkillsWidget[getId, getUpdatedAt]');
        $skillsWidget2->shouldReceive('getId')->andReturn($skillsWidgetId2 = rand(0, PHP_INT_MAX));
        $skillsWidget2->shouldReceive('getUpdatedAt')->andReturn($skillsWidgetUpdatedAt2 = (new \DateTime())->add(new \DateInterval('P2D')));
        $skillsWidget2
            ->setSkills(array($skillsWidgetSkill2))
            ->setLabel($skillsWidgetLabel2 = uniqid());

        $portfolio = new Portfolio();
        $portfolio
            ->setUser($this->createUser($firstname = uniqid(), $lastname = uniqid()))
            ->setWidgets(array($titleWidget, $skillsWidget, $skillsWidget2));

        $actual = $exporter->export($portfolio, 'leap2a');
        $portfolioLastUpdateDate = $skillsWidgetUpdatedAt->format(\DateTime::ATOM);
        $skillsWidgetUpdatedDate2 = $skillsWidgetUpdatedAt2->format(\DateTime::ATOM);
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

    <entry>
        <title>$skillsWidgetLabel</title>
        <id>portfolio:skills/$skillsWidgetId</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:selection"/>
        <category term="Abilities" scheme="categories:selection_type#"/>

        <link rel="leap2:has_part" href="portfolio:skill/$skillsWidgetSkillId" leap2:display_order="1"/>
    </entry>

    <entry>
        <title>$skillsWidgetSkillName</title>
        <id>portfolio:skill/$skillsWidgetSkillId</id>
        <updated>$portfolioLastUpdateDate</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:ability"/>

        <link rel="leap2:is_part_of" href="portfolio:skills/$skillsWidgetId" leap2:display_order="1"/>
    </entry>

    <entry>
        <title>$skillsWidgetLabel2</title>
        <id>portfolio:skills/$skillsWidgetId2</id>
        <updated>$skillsWidgetUpdatedDate2</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:selection"/>
        <category term="Abilities" scheme="categories:selection_type#"/>

        <link rel="leap2:has_part" href="portfolio:skill/$skillsWidgetSkillId2" leap2:display_order="1"/>
    </entry>

    <entry>
        <title>$skillsWidgetSkillName2</title>
        <id>portfolio:skill/$skillsWidgetSkillId2</id>
        <updated>$skillsWidgetUpdatedDate2</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:ability"/>

        <link rel="leap2:is_part_of" href="portfolio:skills/$skillsWidgetId2" leap2:display_order="1"/>
    </entry>
</feed>
EXPORT;

        $this->assertEquals($expected, $actual);
    }

    public function testLeap2aExportPortfolioWithTextWidget()
    {
        $exporter = new Exporter($this->twigEngine);

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\TitleWidget[getUpdatedAt]');
        $titleWidget->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());
        $titleWidget
            ->setTitle($portfolioTitle = uniqid())
            ->setSlug($portfolioSlug = uniqid());

        /** @var \Icap\PortfolioBundle\Entity\Widget\TextWidget $textWidget */
        $textWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\TextWidget[getId, getUpdatedAt]');
        $textWidget->shouldReceive('getId')->andReturn($textWidgetId = rand(0, PHP_INT_MAX));
        $textWidget->shouldReceive('getUpdatedAt')->andReturn($textWidgetUpdatedAt = (new \DateTime())->add(new \DateInterval('P2D')));
        $textWidget
            ->setText($textWidgetText = uniqid())
            ->setLabel($textWidgetLabel = uniqid());

        $portfolio = new Portfolio();
        $portfolio
            ->setUser($this->createUser($firstname = uniqid(), $lastname = uniqid()))
            ->setWidgets(array($titleWidget, $textWidget));

        $actual = $exporter->export($portfolio, 'leap2a');
        $portfolioLastUpdateDate = $titleWidget->getUpdatedAt()->format(\DateTime::ATOM);
        $textWidgetUpdatedAt = $textWidgetUpdatedAt->format(\DateTime::ATOM);
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
    <updated>$textWidgetUpdatedAt</updated>

    <entry>
        <title>$textWidgetLabel</title>
        <id>portfolio:text/$textWidgetId</id>
        <updated>$textWidgetUpdatedAt</updated>
        <content type="html">$textWidgetText</content>
    </entry>
</feed>
EXPORT;

        $this->assertEquals($expected, $actual);
    }

    public function testLeap2aExportPortfolioWithUserInformationsWidget()
    {
        $exporter = new Exporter($this->twigEngine);

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\TitleWidget[getUpdatedAt]');
        $titleWidget->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());
        $titleWidget
            ->setTitle($portfolioTitle = uniqid())
            ->setSlug($portfolioSlug = uniqid());

        /** @var \Icap\PortfolioBundle\Entity\Widget\UserInformationWidget $userInformationsWidget */
        $userInformationsWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\UserInformationWidget[getId, getUpdatedAt]');
        $userInformationsWidget->shouldReceive('getId')->andReturn($userInformationsWidgetId = rand(0, PHP_INT_MAX));
        $userInformationsWidget->shouldReceive('getUpdatedAt')->andReturn($userInformationsWidgetUpdatedAt = (new \DateTime())->add(new \DateInterval('P2D')));
        $userInformationsWidget
            ->setBirthDate($userInformationsWidgetBirthDate = new \DateTime('1986/11/29'))
            ->setCity($userInformationsWidgetCity = uniqid())
            ->setLabel($userInformationsWidgetLabel = uniqid());

        $portfolio = new Portfolio();
        $portfolio
            ->setUser($this->createUser($firstname = uniqid(), $lastname = uniqid()))
            ->setWidgets(array($titleWidget, $userInformationsWidget));

        $actual = $exporter->export($portfolio, 'leap2a');
        $portfolioLastUpdateDate = $titleWidget->getUpdatedAt()->format(\DateTime::ATOM);
        $userInformationsWidgetUpdatedAt = $userInformationsWidgetUpdatedAt->format(\DateTime::ATOM);
        $userInformationsWidgetBirthDate = $userInformationsWidgetBirthDate->format(\DateTime::ATOM);
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
    <updated>$userInformationsWidgetUpdatedAt</updated>

    <entry>
        <title>$userInformationsWidgetLabel</title>
        <id>portfolio:text/$userInformationsWidgetId</id>
        <updated>$userInformationsWidgetUpdatedAt</updated>
        <content></content>
        <rdf:type rdf:resource="leap2:person"/>
        <leap2:persondata leap2:field="dob">$userInformationsWidgetBirthDate</leap2:persondata>
        <leap2:persondata leap2:field="other" leap2:label="city">$userInformationsWidgetCity</leap2:persondata>
    </entry>
</feed>
EXPORT;

        $this->assertEquals($expected, $actual);
    }

    public function testLeap2aExportPortfolioWithOneFormationWidgetWithOneresource()
    {
        $exporter = new Exporter($this->twigEngine);

        /** @var \Icap\PortfolioBundle\Entity\Widget\TitleWidget $titleWidget */
        $titleWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\TitleWidget[getUpdatedAt]');
        $titleWidget->shouldReceive('getUpdatedAt')->andReturn(new \DateTime());
        $titleWidget
            ->setTitle($portfolioTitle = uniqid())
            ->setSlug($portfolioSlug = uniqid());

        /** @var \Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource $formationWidgetResource */
        $formationWidgetResource = $this->mock('Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource[getId]');
        $formationWidgetResource->shouldReceive('getId')->andReturn($formationWidgetResourceId = rand(0, PHP_INT_MAX));

        $formationWidgetResourceResourceNode = new ResourceNode();
        $formationWidgetResourceResourceNode->setId($formationWidgetResourceResourceNodeId = rand(0, PHP_INT_MAX));
        $formationWidgetResourceResourceNode->setModificationDate($formationWidgetResourceUpdatedAt = new \DateTime());
        $formationWidgetResourceResourceNode->setName($formationWidgetResourceName = uniqid());

        $formationWidgetResource
            ->setResource($formationWidgetResourceResourceNode);

        /** @var \Icap\PortfolioBundle\Entity\Widget\FormationsWidget $formationWidget */
        $formationWidget = $this->mock('Icap\PortfolioBundle\Entity\Widget\FormationsWidget[getId, getUpdatedAt]');
        $formationWidget->shouldReceive('getId')->andReturn($formationWidgetId = rand(0, PHP_INT_MAX));
        $formationWidget->shouldReceive('getUpdatedAt')->andReturn($formationWidgetUpdatedAt = (new \DateTime())->add(new \DateInterval('P2D')));
        $formationWidget
            ->setName($formationWidgetName = uniqid())
            ->setStartDate($formationWidgetStartDate = new \DateTime())
            ->setEndDate($formationWidgetEndDate = (new \DateTime())->add(new \DateInterval('P1Y')))
            ->setResources(array($formationWidgetResource))
            ->setLabel($userInformationsWidgetLabel = uniqid());

        $portfolio = new Portfolio();
        $portfolio
            ->setUser($this->createUser($firstname = uniqid(), $lastname = uniqid()))
            ->setWidgets(array($titleWidget, $formationWidget));

        $actual = $exporter->export($portfolio, 'leap2a');
        $portfolioLastUpdateDate = $titleWidget->getUpdatedAt()->format(\DateTime::ATOM);
        $formationWidgetUpdatedAt = $formationWidgetUpdatedAt->format(\DateTime::ATOM);
        $formationWidgetStartDate = $formationWidgetStartDate->format(\DateTime::ATOM);
        $formationWidgetEndDate = $formationWidgetEndDate->format(\DateTime::ATOM);
        $formationWidgetResourceUpdatedAt = $formationWidgetResourceUpdatedAt->format(\DateTime::ATOM);
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
    <updated>$formationWidgetUpdatedAt</updated>

    <entry>
        <title>$userInformationsWidgetLabel</title>
        <id>portfolio:formations/$formationWidgetId</id>
        <updated>$formationWidgetUpdatedAt</updated>
        <content type="text">$formationWidgetName</content>

        <leap2:date leap2:point="start">$formationWidgetStartDate</leap2:date>
        <leap2:date leap2:point="end">$formationWidgetEndDate</leap2:date>

        <rdf:type rdf:resource="leap2:activity"/>

        <link rel="leap2:has_part" href="portfolio:resource/$formationWidgetResourceResourceNodeId" leap2:display_order="1"/>
    </entry>

    <entry>
        <title>$formationWidgetResourceName</title>
        <id>portfolio:resource/$formationWidgetResourceResourceNodeId</id>
        <uri>$formationWidgetResourceResourceNodeId</uri>
        <updated>$formationWidgetResourceUpdatedAt</updated>
        <content></content>

        <rdf:type rdf:resource="leap2:resource"/>
        <category term="Web" scheme="categories:resource_type#"/>

        <link rel="leap2:is_part_of" href="portfolio:formations/$formationWidgetId" leap2:display_order="1"/>
    </entry>
</feed>
EXPORT;

        $this->assertEquals($expected, $actual);
    }
}
