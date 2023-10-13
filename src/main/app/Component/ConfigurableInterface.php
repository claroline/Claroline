<?php

namespace Claroline\AppBundle\Component;

/**
 * ConfigurableInterface is the interface implemented by claroline components
 * which can be configured.
 */
interface ConfigurableInterface
{
    public function configure(array $configurationData): void;
}
