<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotNullType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default' => null,
        ]);

        $resolver->setAllowedValues('default', [null, true, false]);
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {

        $requestValue = null === $finder->getFilterValue() ? null : filter_var($finder->getFilterValue(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $value = null === $requestValue ? $options['default'] : $requestValue;

        if (null !== $value) {
            if ($value) {
                $queryBuilder->andWhere("{$finder->getQueryPath()} IS NOT NULL");
            } else {
                $queryBuilder->andWhere("{$finder->getQueryPath()} IS NULL");
            }
        }
    }
}
