<?php

namespace Icap\PortfolioBundle\Importer;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\AbstractWidget;
use Icap\PortfolioBundle\Entity\Widget\FormationsWidget;
use Icap\PortfolioBundle\Entity\Widget\FormationsWidgetResource;
use Icap\PortfolioBundle\Entity\Widget\SkillsWidget;
use Icap\PortfolioBundle\Entity\Widget\SkillsWidgetSkill;
use Icap\PortfolioBundle\Entity\Widget\TextWidget;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;
use Icap\PortfolioBundle\Entity\Widget\UserInformationWidget;
use Icap\PortfolioBundle\Transformer\XmlToArray;

class Leap2aImporter implements  ImporterInterface
{
    const IMPORT_FORMAT = 'leap2a';
    const IMPORT_FORMAT_LABEL = 'Leap2a';

    /**
     * @return string
     */
    public function getFormat()
    {
        return self::IMPORT_FORMAT;
    }

    /**
     * @return string
     */
    public function getFormatLabel()
    {
        return self::IMPORT_FORMAT_LABEL;
    }

    /**
     * @param string $content
     * @param User   $user
     *
     * @return \Icap\PortfolioBundle\Entity\Portfolio
     * @throws \InvalidArgumentException
     */
    public function import($content, User $user)
    {
        $content = str_replace('xmlns=', 'ns=', $content);
        $xml = new \SimpleXMLElement($content);

        $portfolio = $this->retrievePortfolioFromXml($xml, $user);

        return $portfolio;
    }

    /**
     * @param \SimpleXMLElement $xml
     * @param User              $user
     *
     * @return Portfolio
     * @throws \Exception
     */
    public function retrievePortfolioFromXml(\SimpleXMLElement $xml, User $user)
    {
        $portfolioTitleNodes = $xml->xpath('/feed/title');

        if (0 === count($portfolioTitleNodes)) {
            throw new \Exception("Missing portfolio's title");
        }
        $portfolioTitle = (string)$portfolioTitleNodes[0];

        $titleWidget = new TitleWidget();
        $titleWidget->setTitle($portfolioTitle);

        $widgets   = $this->retrieveWidgets($xml);
        $widgets[] = $titleWidget;

        $portfolio = new Portfolio();
        $portfolio
            ->setUser($user)
            ->setWidgets($widgets);

        return $portfolio;
    }

    /**
     * @param \SimpleXmlElement $nodes
     *
     * @return \Icap\PortfolioBundle\Entity\Widget\AbstractWidget[]
     * @throws \Exception
     */
    public function retrieveWidgets(\SimpleXMLElement $nodes)
    {
        $skillsWidgets = $this->extractSkillsWidget($nodes);

//        $userInformationWidgets = $this->extractUserInformationWidget($nodes);

//        $formationWidgets = $this->extractFormationsWidget($nodes);

//        $textWidgets = $this->extractTextWidget($nodes);

        return $skillsWidgets;
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

    /**
     * @param \SimpleXMLElement $nodes
     *
     * @return SkillsWidget
     * @throws \Exception
     */
    protected function extractSkillsWidget(\SimpleXMLElement $nodes)
    {
        $skillsWidgets = array();

        $skillsWidgetNodes = $nodes->xpath(("//entry[rdf:type/@rdf:resource = 'leap2:selection' and category/@term = 'Abilities']"));
        foreach ($skillsWidgetNodes as $skillsWidgetNode) {
            $skillsWidget = new SkillsWidget();
            $skillsWidgetTitle = $skillsWidgetNode->xpath("title");

            if (0 === count($skillsWidgetTitle)) {
                throw new \Exception('Entry has no title.');
            }

            $skillsWidget->setLabel((string)$skillsWidgetTitle[0]);

            $skillsWidgetSkills = array();
            $relatedSkills = array();

            foreach ($skillsWidgetNode->xpath("link[@rel='leap2:has_part']/@href") as $relatedSkillAttributes) {
                $relatedSkillArrayAttributes = (array)$relatedSkillAttributes;
                $relatedSkillNodes = $nodes->xpath(sprintf("entry[id[.='%s']]", $relatedSkillArrayAttributes['@attributes']['href']));

                if (0 === count($relatedSkillNodes)) {
                    throw new \Exception("Unable to find skills.");
                }
                $relatedSkillNode = (array)$relatedSkillNodes[0];

                $relatedSkillNodeLink = (array)$relatedSkillNode['link'];

                if ((string)$skillsWidgetNode->xpath("id")[0] !== $relatedSkillNodeLink['@attributes']['href']) {
                    throw new \Exception("Inconsistency in skills relation.");
                }

                $skillsWidgetSkill = new SkillsWidgetSkill();
                $skillsWidgetSkill->setName($relatedSkillNode['title']);

                $skillsWidgetSkills[] = $skillsWidgetSkill;
            }
            $skillsWidget->setSkills($skillsWidgetSkills);
            $skillsWidgets[] = $skillsWidget;
        }

        return $skillsWidgets;
    }

    /**
     * @param array $entries
     * @param array $entry
     *
     * @return FormationsWidget
     * @throws \Exception
     */
    protected function extractFormationsWidget(array $entries, array $entry)
    {
        $formationsWidgetResources = array();
        $formationsWidgetId = $entry['id']['$'];

        foreach ($entries as $subEntry) {
            $this->validateEntry($subEntry);

            if ('leap2:resource' === $subEntry['rdf:type']['@rdf:resource']) {
                $selfLink = null;

                foreach ($subEntry['link'] as $entryLink) {
                    $selfLinkKey = array_search('self', $entryLink);
                    if (false !== $selfLinkKey) {
                        $selfLink = $entryLink['@href'];
                    }
                    $isPartOfLinkKey = array_search('leap2:is_part_of', $entryLink);
                    if (false !== $isPartOfLinkKey) {
                        $isPartOfLink = $entryLink['@href'];
                    }
                }
                if (null === $selfLink) {
                    throw new \Exception('Unable to find self link for the resource.');
                }
                if (null === $isPartOfLink) {
                    throw new \Exception('Unable to find is_part_of link for the resource.');
                }

                if ($formationsWidgetId === $isPartOfLink) {
                    $formationsWidgetResource = new FormationsWidgetResource();
                    $formationsWidgetResource
                        ->setUriLabel($subEntry['title']['$'])
                        ->setUri($selfLink);

                    $formationsWidgetResources[] = $formationsWidgetResource;
                }
            }
        }

        $formationsWidget = new FormationsWidget();
        $formationsWidget
            ->setLabel($entry['title']['$'])
            ->setResources($formationsWidgetResources);

        return $formationsWidget;
    }

    /**
     * @param array $entry
     *
     * @return UserInformationWidget
     */
    private function extractUserInformationWidget(array $entry)
    {
        $userInformationWidget = new UserInformationWidget();
        $userInformationWidget->setLabel($entry['title']['$']);

        foreach ($entry['leap2:persondata'] as $personData) {
            switch($personData['@leap2:field']) {
                case 'dob':
                    $userInformationWidget->setBirthDate(new \DateTime($personData['$']));
                    break;
                case 'other':
                    switch($personData['@leap2:label']) {
                        case 'city':
                            $userInformationWidget->setCity($personData['$']);
                            break;
                    }
                    break;
            }
        }

        return $userInformationWidget;
    }

    /**
     * @param array $entry
     *
     * @return UserInformationWidget
     */
    private function extractTextWidget(array $entry)
    {
        $TextWidget = new TextWidget();
        $TextWidget
            ->setLabel($entry['title']['$'])
            ->setText($entry['content']['$']);

        return $TextWidget;
    }
}
