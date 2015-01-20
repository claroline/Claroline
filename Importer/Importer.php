<?php

namespace Icap\PortfolioBundle\Importer;

class Importer
{
    protected $availableFormats = array('leap2a');

    public function import($content, $format)
    {
        if (!$this->isFormatAvailable($format)) {
            throw new \InvalidArgumentException('Unknown format.');
        }
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
