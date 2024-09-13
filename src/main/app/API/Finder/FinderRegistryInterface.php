<?php

namespace Claroline\AppBundle\API\Finder;

interface FinderRegistryInterface
{
    /**
     * Returns a finder type by name.
     *
     * This method registers the type extensions from the form extensions.
     *
     * @throws \InvalidArgumentException if the type cannot be retrieved from any extension
     */
    public function getType(string $name): ResolvedFinderTypeInterface;

    /**
     * Returns whether the given finder type is supported.
     */
    public function hasType(string $name): bool;
}
