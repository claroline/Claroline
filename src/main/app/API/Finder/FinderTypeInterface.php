<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

interface FinderTypeInterface
{
    /**
     * Define options for the Finder.
     */
    public function configureOptions(OptionsResolver $resolver): void;

    /**
     * Submit a filter value to the Finder. This the place to append default, validate and parse filter value.
     */
    public function submit(mixed $filterValue, array $options): mixed;

    /**
     * Create the definition of the Finder.
     */
    public function buildFinder(FinderBuilderInterface $builder, array $options): void;

    /**
     * Create the final DQL query for the Finder.
     */
    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void;

    /**
     * Get the class of the parent type.
     */
    public function getParent(): ?string;
}
