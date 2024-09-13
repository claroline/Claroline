<?php

namespace Claroline\AppBundle\API\Finder;

interface FinderBuilderInterface
{
    public function getOptions(): array;

    public function add(string $name, ?string $type = null, ?array $options = []): static;

    public function get(string $name): FinderBuilderInterface;

    /**
     * Creates the finder.
     */
    public function getFinder(): FinderInterface;
}
