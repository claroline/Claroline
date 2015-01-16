<?php

namespace Icap\PortfolioBundle\Exporter;

use Icap\PortfolioBundle\Entity\Portfolio;
use Symfony\Component\Templating\EngineInterface;

class Exporter
{
    protected $availableFormats = array('leap2a');

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
        if (!$this->isFormatAvailable($format)) {
            throw new \InvalidArgumentException('Unknown format.');
        }

        return $this->templatingEngine->render(sprintf('IcapPortfolioBundle:Exporter:export.%s.twig', $format), array('portfolio' => $portfolio));
    }

    /**
     * @param $format
     *
     * @return boolean
     */
    protected function isFormatAvailable($format) {
        return in_array($format, $this->availableFormats);
    }
}
