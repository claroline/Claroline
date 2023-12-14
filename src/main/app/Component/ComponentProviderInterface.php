<?php

namespace Claroline\AppBundle\Component;

interface ComponentProviderInterface
{
    /**
     * The symfony service tag each provided component MUST have.
     */
    public static function getServiceTag(): string;

    /**
     * Get a registered component from the provider.
     */
    public function getComponent(string $componentName): ComponentInterface;
}
