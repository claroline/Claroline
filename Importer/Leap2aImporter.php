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
        $content = str_replace('xmlns=', 'ns=', $content);// For getting something from the \SimpleXMLElement
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
        $skillsWidgets = $this->extractSkillsWidgets($nodes);

        $userInformationWidgets = $this->extractUserInformationWidgets($nodes);

        $formationWidgets = $this->extractFormationsWidgets($nodes);

        $textWidgets = $this->extractTextWidgets($nodes);

        return $skillsWidgets + $userInformationWidgets + $formationWidgets + $textWidgets;
    }

    /**
     * @param \SimpleXMLElement $nodes
     *
     * @return SkillsWidget[]
     * @throws \Exception
     */
    protected function extractSkillsWidgets(\SimpleXMLElement $nodes)
    {
        $skillsWidgets     = [];
        $skillsWidgetNodes = $nodes->xpath("//entry[rdf:type/@rdf:resource = 'leap2:selection' and category/@term = 'Abilities']");

        foreach ($skillsWidgetNodes as $skillsWidgetNode) {
            $skillsWidget = new SkillsWidget();
            $skillsWidgetTitle = $skillsWidgetNode->xpath("title");

            if (0 === count($skillsWidgetTitle)) {
                throw new \Exception('Entry has no title.');
            }

            $skillsWidget->setLabel((string)$skillsWidgetTitle[0]);

            $skillsWidgetSkills = [];

            foreach ($skillsWidgetNode->xpath("link[@rel='leap2:has_part']/@href") as $relatedSkillAttributes) {
                $relatedSkillArrayAttributes = (array)$relatedSkillAttributes;
                $relatedSkillNodes = $nodes->xpath(sprintf("entry[id[.='%s']]", $relatedSkillArrayAttributes['@attributes']['href']));

                if (0 === count($relatedSkillNodes)) {
                    throw new \Exception("Unable to find skills.");
                }
                $relatedSkillNode = (array)$relatedSkillNodes[0];

                $relatedSkillNodeLink = (array)$relatedSkillNode['link'];

                if ((string)$skillsWidgetNode->xpath("id")[0] !== $relatedSkillNodeLink['@attributes']['href']) {
                    throw new \Exception("Inconsistency in skills relation for skills widget.");
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
     * @param \SimpleXMLElement $nodes
     *
     * @return FormationsWidget[]
     * @throws \Exception
     */
    protected function extractFormationsWidgets(\SimpleXMLElement $nodes)
    {
        $formationsWidgets      = [];
        $formationsWidgetsNodes = $nodes->xpath("//entry[rdf:type/@rdf:resource = 'leap2:activity' and category/@term = 'Education']");

        foreach ($formationsWidgetsNodes as $formationsWidgetsNode) {
            $formationsWidget = new FormationsWidget();
            $formationsWidgetTitle = $formationsWidgetsNode->xpath("title");

            if (0 === count($formationsWidgetTitle)) {
                throw new \Exception('Entry has no title.');
            }

            $formationsWidget->setLabel((string)$formationsWidgetTitle[0]);

            $formationsWidgetResources = [];

            foreach ($formationsWidgetsNode->xpath("link[@rel='leap2:has_part']/@href") as $relatedResourceAttributes) {
                $relatedResourceArrayAttributes = (array)$relatedResourceAttributes;
                $relatedResourceNodes = $nodes->xpath(sprintf("entry[id[.='%s']]", $relatedResourceArrayAttributes['@attributes']['href']));

                if (0 === count($relatedResourceNodes)) {
                    throw new \Exception("Unable to find resources.");
                }
                $relatedResourceNode = $relatedResourceNodes[0];

                $relatedResourceHrefNodes = $relatedResourceNode->xpath("link[@rel='leap2:is_part_of']/@href");

                if (0 === $relatedResourceHrefNodes) {
                    throw new \Exception("Inconsistency in resources relation, resource isn't related to any formation widget.");
                }

                $relatedResourceHrefArrayNodes = (array)$relatedResourceHrefNodes[0];

                if ((string)$formationsWidgetsNode->xpath("id")[0] !== $relatedResourceHrefArrayNodes['@attributes']['href']) {
                    throw new \Exception("Inconsistency in resources relation for formation widget.");
                }

                $relatedResourceSelfHrefNodes = $relatedResourceNode->xpath("link[@rel='self']/@href");

                if (0 === $relatedResourceSelfHrefNodes) {
                    throw new \Exception("Resource doesn't have a self link.");
                }

                $relatedResourceSelfHrefArrayNodes = (array)$relatedResourceSelfHrefNodes[0];
                $formationsWidgetResource = new FormationsWidgetResource();
                $formationsWidgetResource
                    ->setUriLabel($relatedResourceNode->title)
                    ->setUri($relatedResourceSelfHrefArrayNodes['@attributes']['href']);

                $formationsWidgetResources[] = $formationsWidgetResource;
            }
            $formationsWidget->setResources($formationsWidgetResources);
            $formationsWidgets[] = $formationsWidget;
        }

        return $formationsWidgets;
    }

    /**
     * @param \SimpleXMLElement $nodes
     *
     * @return UserInformationWidget
     */
    private function extractUserInformationWidgets(\SimpleXMLElement $nodes)
    {
        $userInformationWidgetNodes = $nodes->xpath("//entry[rdf:type/@rdf:resource = 'leap2:person']");
        $userInformationWidgets     = [];

        if (0 < count($userInformationWidgetNodes)) {
            $birthDateNode = $userInformationWidgetNodes[0]->xpath("leap2:persondata[@leap2:field ='dob']");
            $birthDate     = (string)$birthDateNode[0];

            $cityNode = $userInformationWidgetNodes[0]->xpath("leap2:persondata[@leap2:field = 'other' and @leap2:label = 'city']");
            $city     = (string)$cityNode[0];

            $userInformationWidget = new UserInformationWidget();
            $userInformationWidget
                ->setLabel((string)$userInformationWidgetNodes[0]->title)
                ->setBirthDate(new \DateTime($birthDate))
                ->setCity($city);

            $userInformationWidgets[] = $userInformationWidget;
        }

        return $userInformationWidgets;
    }

    /**
     * @param \SimpleXMLElement $nodes
     *
     * @return TextWidget[]
     */
    private function extractTextWidgets(\SimpleXMLElement $nodes)
    {
        $textWidgets     = [];
        $textWidgetNodes = $nodes->xpath("//entry[rdf:type/@rdf:resource = 'leap2:entry']");

        foreach ($textWidgetNodes as $textWidgetNode) {
            $textWidget = new TextWidget();
            $textWidget
                ->setLabel((string)$textWidgetNode->title)
                ->setText((string)$textWidgetNode->content);

            $textWidgets[] = $textWidget;
        }

        return $textWidgets;
    }
}
