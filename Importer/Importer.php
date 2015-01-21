<?php

namespace Icap\PortfolioBundle\Importer;

use Claroline\CoreBundle\Entity\User;
use Icap\PortfolioBundle\Entity\Portfolio;
use Icap\PortfolioBundle\Entity\Widget\TitleWidget;

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

        $portfolio = $this->arrayToPortfolio($arrayContent);
        $portfolio->setUser($user);

        return $portfolio;
    }

    /**
     * @param string $content
     * @param string $format
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function transformContent($content, $format)
    {
        switch($format) {
            case self::IMPORT_FORMAT_LEAP2A:
                $xml   = simplexml_load_string($content);
                $json  = json_encode($xml);
                $transformedContent = json_decode($json, true);
                break;
            default:
                throw new \InvalidArgumentException('Cannot transform unknown format.');
        }

        return $transformedContent;
    }

    /**
     * @param array $arrayContent
     *
     * @return Portfolio
     * @throws \Exception
     */
    public function arrayToPortfolio(array $arrayContent)
    {
        $portfolio = new Portfolio();
        $widgets = array();

        if (!isset($arrayContent['title'])) {
            throw new \Exception("Missing portfolio's title");
        }
        $titleWidget = new TitleWidget();
        $titleWidget->setTitle($arrayContent['title']);
        $widgets[] = $titleWidget;

        $portfolio->setWidgets($widgets);

        return $portfolio;
    }
}
