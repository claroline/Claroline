<?php

namespace Icap\PortfolioBundle\Importer;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
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
                $transformedContent = $xmlToArrayTransformer->transform($content)['feed'];
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
        $widgets = array();

        if (!isset($arrayContent['title'])) {
            throw new \Exception("Missing portfolio's title");
        }
        $titleWidget = new TitleWidget();
        $titleWidget->setTitle($arrayContent['title']['$']);
        $widgets[] = $titleWidget;

        $portfolio->setWidgets($widgets);

        return $portfolio;
    }

    /**
     * @param array $entries
     *
     * @return array
     */
    public function retrieveWidgets(array $entries)
    {
        $widgets = array();

//        echo "<pre>";
//        var_dump($entries);
//        echo "</pre>" . PHP_EOL;

        foreach ($entries as $entry) {
//            echo "<pre>";
//            var_dump($entry);
//            echo "</pre>" . PHP_EOL;
        }

        return $widgets;
    }
}
