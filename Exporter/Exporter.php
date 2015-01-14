<?php

namespace Icap\PortfolioBundle\Exporter;

use Icap\PortfolioBundle\Entity\Portfolio;

class Exporter
{
    protected $availableFormats = array('leap2a');

    public function export(Portfolio $portfolio, $format)
    {
        if (!$this->isFormatAvailable($format)) {
            throw new \InvalidArgumentException('Unknown format.');
        }

        return 'pouet';
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
