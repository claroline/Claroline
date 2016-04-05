<?php

namespace Icap\PortfolioBundle\Exporter;

use Icap\PortfolioBundle\Entity\Portfolio;
use Symfony\Component\Templating\EngineInterface;

class Exporter
{
    const EXPORT_FORMAT_LEAP2A = 'leap2a';
    protected $availableFormats = array(self::EXPORT_FORMAT_LEAP2A);

    /**
     * @var EngineInterface
     */
    protected $templatingEngine;

    public function __construct(EngineInterface $templatingEngine)
    {
        $this->templatingEngine = $templatingEngine;
    }

    public function export(Portfolio $portfolio, $format)
    {
        if (!in_array($format, $this->availableFormats)) {
            throw new \InvalidArgumentException('Unknown format.');
        }

        return $this->templatingEngine->render(sprintf('IcapPortfolioBundle:export:export.%s.twig', $format), array('portfolio' => $portfolio));
    }


}
