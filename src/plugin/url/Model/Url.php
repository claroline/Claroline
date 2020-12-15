<?php

namespace HeVinci\UrlBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait Url
{
    /**
     * @ORM\Column(name="url", type="text")
     *
     * @var string
     */
    private $url;

    /**
     * @ORM\Column(type="string")
     *
     * @var string
     */
    private $mode = 'redirect';

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @var float
     */
    private $ratio = 56.25;

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setMode(string $mode)
    {
        $this->mode = $mode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setRatio(float $ratio)
    {
        $this->ratio = $ratio;
    }

    public function getRatio(): ?float
    {
        return $this->ratio;
    }
}
