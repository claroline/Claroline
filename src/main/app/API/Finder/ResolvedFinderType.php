<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResolvedFinderType implements ResolvedFinderTypeInterface
{
    private ?OptionsResolver $optionsResolver = null;

    public function __construct(
        private readonly FinderTypeInterface $innerType,
        private readonly ?ResolvedFinderTypeInterface $parent
    ) {
    }

    public function getParent(): ?ResolvedFinderTypeInterface
    {
        return $this->parent;
    }

    public function getInnerType(): FinderTypeInterface
    {
        return $this->innerType;
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $this->parent?->buildFinder($builder, $options);
        $this->innerType->buildFinder($builder, $options);
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        $this->parent?->buildQuery($queryBuilder, $finder, $options);
        $this->innerType->buildQuery($queryBuilder, $finder, $options);
    }

    public function getOptionsResolver(): OptionsResolver
    {
        if (!isset($this->optionsResolver)) {
            if (null !== $this->parent) {
                $this->optionsResolver = clone $this->parent->getOptionsResolver();
            } else {
                $this->optionsResolver = new OptionsResolver();
            }

            $this->innerType->configureOptions($this->optionsResolver);
        }

        return $this->optionsResolver;
    }
}
