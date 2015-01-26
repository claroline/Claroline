<?php

namespace Icap\PortfolioBundle\Importer;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Icap\PortfolioBundle\Entity\Widget\SkillsWidget;
use Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Transformer\XmlToArray;

class Importer
{
    const IMPORT_FORMAT_LEAP2A = 'leap2a';
    protected $availableFormats = array(self::IMPORT_FORMAT_LEAP2A);

    /**
     * @param string $content
     * @param string $format
     * @param User   $user
     *
     * @return \Icap\PortfolioBundle\Entity\Portfolio
     * @throws \InvalidArgumentException
     */
    public function import($content, $format, User $user)
    {
        if (!in_array($format, $this->availableFormats)) {
            throw new \InvalidArgumentException('Unknown format.');
        }

        $arrayContent = $this->transformContent($content, $format);

        $portfolio = $this->arrayToPortfolio($arrayContent, $user);

        return $portfolio;
    }

    /**
     * @param string $content
     * @param string $format
     *
     * @return array
     * @throws \Exception
     * @throws \InvalidArgumentException
     */
    public function transformContent($content, $format)
    {
        switch($format) {
            case self::IMPORT_FORMAT_LEAP2A:
                $xmlToArrayTransformer = new XmlToArray();
                $transformedContent = $xmlToArrayTransformer->transform($content);
                break;
            default:
                throw new \InvalidArgumentException('Cannot transform unknown format.');
        }

        return $transformedContent;
    }

    /**
     * @param array $arrayContent
     * @param User  $user
     *
     * @return Portfolio
     * @throws \Exception
     */
    public function arrayToPortfolio(array $arrayContent, User $user)
    {
        $portfolio = new Portfolio();
        $portfolio->setUser($user);

        if (!isset($arrayContent['title'])) {
            throw new \Exception("Missing portfolio's title");
        }
        $titleWidget = new TitleWidget();
        $titleWidget->setTitle($arrayContent['title']['$']);

        $widgets = array();

        if (isset($arrayContent['entry'])) {
            $widgets = $this->retrieveWidgets($arrayContent['entry']);
        }

        $widgets[] = $titleWidget;

        $portfolio->setWidgets($widgets);

        return $portfolio;
    }

    /**
     * @param array $entries
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[]
     * @throws \Exception
     */
    public function retrieveWidgets(array $entries)
    {
        $widgets = array();

        foreach ($entries as $entry) {
            $this->validateEntry($entry);

            $entryType = $entry['rdf:type']['@rdf:resource'];
            $entryCategory = isset($entry['category']) ? $entry['category']['@term'] : null;

            $widgetType = $this->getWidgetType($entryType, $entryCategory);

            if (null !== $widgetType) {
                switch($widgetType) {
                    case 'skills':
                        $skillsWidgetSkills = array();

                        foreach ($entries as $subEntry) {
                            $this->validateEntry($subEntry);

                            if ('leap2:ability' === $subEntry['rdf:type']['@rdf:resource']) {
                                $skillsWidgetSkill = new SkillsWidgetSkill();
                                $skillsWidgetSkill->setName($subEntry['title']);

                                $skillsWidgetSkills[] = $skillsWidgetSkill;
                            }
                        }

                        $skillsWidget = new SkillsWidget();
                        $skillsWidget
                            ->setLabel($entry['title'])
                            ->setSkills($skillsWidgetSkills);

                        $widgets[] = $skillsWidget;
                        break;
                    case 'userInformation':
                        break;
                    case 'formations':
                        break;
                    case 'text':
                        break;
                    case 'badges':
                        break;
                    default:
                        throw new \Exception(sprintf("Unknown widget type '%s'.", $widgetType));
                }
            }
        }

//        die("FFFFFUUUUUCCCCCKKKKK" . PHP_EOL);
        return $widgets;
    }

    /**
     * @param array $entry
     *
     * @throws \Exception
     */
    protected function validateEntry(array $entry)
    {
        if (!isset($entry['rdf:type'])) {
            throw new \Exception('Entry type missing.');
        }
        if (!isset($entry['rdf:type']['@rdf:resource'])) {
            throw new \Exception('Entry type missing.');
        }
    }

    /**
     * @param string      $entryType
     * @param null|string $entryCategory
     *
     * @return null|string
     */
    protected function getWidgetType($entryType, $entryCategory)
    {
        return ('leap2:selection' === $entryType && null !== $entryCategory && 'Abilities' === $entryCategory) ? 'skills' : (
                    ('leap2:person' === $entryType) ? 'userInformation' : (
                        ('leap2:activity' && null !== $entryCategory && 'Education' === $entryCategory) ? 'formations' : (
                            ('leap2:entry' === $entryType) ? 'text' : (
                                ('leap2:selection' === $entryType && null !== $entryCategory && 'Grouping' === $entryCategory) ? 'badges' : null
                            )
                        )
                    )
                );
    }
}
