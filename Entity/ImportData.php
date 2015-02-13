<?php

namespace Icap\PortfolioBundle\Entity;

class ImportData
{
    /**
     * @var string
     */
    protected $format;

    /**
     * @var string
     */
    protected $content;

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     *
     * @return ImportData
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return ImportData
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }
}