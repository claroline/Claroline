<?php

namespace Claroline\CoreBundle\Entity\Context;

abstract class AbstractContextParameters
{
    private ?string $opening;
    private bool $brand = true;
    private bool $breadcrumbs = true;
    private ?string $menu;

    // contact email
    // help url
    // poster
    // shortcuts
    // footer
    // terms and service ?

    public function getOpening(): string
    {
        return $this->opening;
    }

    public function hasBrand(): bool
    {
        return $this->brand;
    }

    public function hasBreadcrumbs(): bool
    {
        return $this->breadcrumbs;
    }

    public function getMenu(): ?string
    {
        return $this->menu;
    }
}
