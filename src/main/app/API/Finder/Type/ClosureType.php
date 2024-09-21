<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The callback type is a joker type to directly modify the {@see QueryBuilder} without having to register a new FinderType.
 * It's useful to make filter based on computed values.
 */
class ClosureType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->define('buildQuery')
            ->allowedTypes('Closure')
            ->required();
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        $options['buildQuery']($queryBuilder, $finder, $options);
    }
}
