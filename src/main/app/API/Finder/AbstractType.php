<?php

namespace Claroline\AppBundle\API\Finder;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractType implements FinderTypeInterface
{
    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
    }

    public function getParent(): ?string
    {
        return null;
    }
}
