<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BooleanType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default' => null,
        ]);

        $resolver->setAllowedValues('default', [null, true, false]);
    }

    public function submit(mixed $filterValue, array $options): ?bool
    {
        $requestValue = null;
        if (null !== $filterValue) {
            $requestValue = filter_var($filterValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return null === $requestValue ? $options['default'] : $requestValue;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy($finder->getQueryPath(), $finder->getSortValue());
        }

        if (null !== $finder->getFilterValue()) {
            $queryBuilder->andWhere("{$finder->getQueryPath()} = :{$finder->getAlias()}");
            $queryBuilder->setParameter($finder->getAlias(), $finder->getFilterValue());
        }
    }
}
