<?php

namespace Claroline\AppBundle\API\Finder;

interface FinderFactoryInterface
{
    public function create(string $type, ?array $options = []): FinderInterface;
    public function createBuilder(string $type, ?array $options = []): FinderBuilderInterface;
    public function createNamedBuilder(string $name, string $type, ?array $options = []): FinderBuilderInterface;
}
