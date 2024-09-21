<?php

namespace HeVinci\UrlBundle\Model;

use Doctrine\ORM\Mapping as ORM;

trait Url
{
    #[ORM\Column(name: 'url', type: 'text')]
    private ?string $url;

    #[ORM\Column(type: 'string')]
    private ?String $mode = 'redirect';

    #[ORM\Column(type: 'float', nullable: true)]
    private ?float $ratio = 56.25;

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setRatio(?float $ratio = null): void
    {
        $this->ratio = $ratio;
    }

    public function getRatio(): ?float
    {
        return $this->ratio;
    }
}
