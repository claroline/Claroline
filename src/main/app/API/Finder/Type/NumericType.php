<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NumericType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default' => null,
        ]);

        $resolver->setAllowedTypes('default', ['null', 'numeric']);
    }

    public function submit(mixed $filterValue, array $options): ?float
    {
        $value = $options['default'];
        if (is_numeric($filterValue)) {
            // convert numbers
            $floatValue = floatval($filterValue);
            if ($filterValue === $floatValue.'') {
                $value = $filterValue;
            }
        }

        return $value;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy($finder->getQueryPath(), $finder->getSortValue());
        }

        if ($finder->hasFilter()) {
            $queryBuilder->andWhere("{$finder->getQueryPath()} = :{$finder->getAlias()}");
            $queryBuilder->setParameter($finder->getAlias(), $finder->getFilterValue());
        }
    }
}
