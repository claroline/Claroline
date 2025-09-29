<?php

namespace Claroline\AppBundle\Entity\Meta;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait Views
{
    #[ORM\Column(name: 'views', type: Types::INTEGER, nullable: false, options: ['default' => 0])]
    protected int $views = 0;

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    public function addView(): void
    {
        ++$this->views;
    }
}
