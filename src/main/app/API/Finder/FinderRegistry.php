<?php

namespace Claroline\AppBundle\API\Finder;

/**
 * The central registry of the Finder component.
 */
class FinderRegistry implements FinderRegistryInterface
{
    /**
     * @var ResolvedFinderTypeInterface[]
     */
    private array $resolvedTypes = [];

    public function __construct(
        private readonly iterable $finderTypes
    ) {
    }

    public function getType(string $name): ResolvedFinderTypeInterface
    {
        if (!isset($this->resolvedTypes[$name])) {
            if (!class_exists($name)) {
                throw new \InvalidArgumentException(sprintf('Could not load type "%s": class does not exist.', $name));
            }

            if (!is_subclass_of($name, FinderTypeInterface::class)) {
                throw new \InvalidArgumentException(sprintf('Could not load type "%s": class does not implement "Claroline\AppBundle\API\Finder\FinderTypeInterface".', $name));
            }

            $type = null;
            foreach ($this->finderTypes as $finderType) {
                if (is_a($finderType, $name)) {
                    $type = $finderType;
                    break;
                }
            }

            if (null === $type) {
                throw new \InvalidArgumentException(sprintf('Could not load type "%s": service does not exist. Maybe you forgot to add the "claroline.finder.type" tag to your type.', $name));
            }

            $this->resolvedTypes[$name] = $this->resolveType($type);
        }

        return $this->resolvedTypes[$name];
    }

    public function hasType(string $name): bool
    {
        if (isset($this->types[$name])) {
            return true;
        }

        try {
            $this->getType($name);
        } catch (\InvalidArgumentException) {
            return false;
        }

        return true;
    }

    /**
     * Wraps a type into a ResolvedFormTypeInterface implementation and connects it with its parent type.
     */
    private function resolveType(FinderTypeInterface $type): ResolvedFinderTypeInterface
    {
        $parentType = $type->getParent();

        return new ResolvedFinderType($type, $parentType ? $this->getType($parentType) : null);
    }
}
