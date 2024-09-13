<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Apply the Hidden filter to a Finder.
 * By default, hidden elements are not retrieved. You can include them by setting the filter to true.
 *
 * NB. A filter with true value will also return the visible elements, not only the hidden ones.
 */
class HiddenType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default' => false,
        ]);

        $resolver->setAllowedTypes('default', ['bool']);
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        $requestValue = filter_var($finder->getFilterValue(), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $value = null === $requestValue ? $options['default'] : $requestValue;

        if (!$value) {
            $queryBuilder->andWhere("{$finder->getQueryPath()} = :{$finder->getAlias()}");
            $queryBuilder->setParameter($finder->getAlias(), $value);
        }
    }
}
