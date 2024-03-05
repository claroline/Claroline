<?php

namespace Claroline\CoreBundle\Entity\Context;

abstract class AbstractContextParameters
{
    /**
     * Open the details page of the context.
     */
    public const OPEN_DEFAULT = 'default';

    /**
     * Open the selected tool when the context is opened.
     */
    public const OPEN_TOOL = 'tool';

    /**
     * Open the selected resource when the context is opened.
     */
    public const OPEN_RESOURCE = 'resource';

    private string $opening = self::OPEN_DEFAULT;

    private ?string $openingTarget = null;

    private bool $brand = true;
    private array $breadcrumbs = [];
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

    public function getBreadcrumbs(): array
    {
        return $this->breadcrumbs;
    }

    public function getMenu(): ?string
    {
        return $this->menu;
    }
}
