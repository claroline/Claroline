<?php

namespace Claroline\AppBundle\API\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class CrudEntity
{
    public function __construct(
        private readonly ?string $serializerClass = null,
        private readonly ?string $finderClass = null,
        private readonly ?string $schema = null,
        private readonly ?bool $hasLifecycleEvents = true
    ) {
    }

    public function getSerializerClass(): ?string
    {
        return $this->serializerClass;
    }

    public function getFinderClass(): ?string
    {
        return $this->finderClass;
    }

    public function getSchema(): ?string
    {
        return $this->schema;
    }

    public function hasLifecycleEvents(): bool
    {
        return $this->hasLifecycleEvents;
    }
}
