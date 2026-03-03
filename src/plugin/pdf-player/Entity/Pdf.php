<?php

namespace Claroline\PdfPlayerBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'claro_pdf_resource')]
class Pdf extends AbstractResource
{
    #[ORM\Column(nullable: false)]
    private ?string $url = null;

    #[ORM\Column(nullable: false)]
    private ?string $scrollMode = 'PAGE';

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getScrollMode(): ?string
    {
        return $this->scrollMode;
    }

    public function setScrollMode(string $scrollMode): void
    {
        $this->scrollMode = $scrollMode;
    }
}
