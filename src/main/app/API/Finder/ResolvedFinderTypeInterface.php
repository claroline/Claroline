<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface ResolvedFinderTypeInterface
{
    /**
     * Returns the parent type.
     */
    public function getParent(): ?ResolvedFinderTypeInterface;

    /**
     * Returns the wrapped finder type.
     */
    public function getInnerType(): FinderTypeInterface;

    /**
     * Configures a finder builder for the type hierarchy.
     */
    public function buildFinder(FinderBuilder $builder, array $options): void;

    /**
     * Configures a finder query for the type hierarchy.
     */
    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void;

    /**
     * Returns the configured options resolver used for this type.
     */
    public function getOptionsResolver(): OptionsResolver;
}
