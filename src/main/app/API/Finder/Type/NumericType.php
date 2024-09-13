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
            'default' => null
        ]);

        $resolver->setAllowedTypes('default', ['null', 'numeric']);
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        $value = $options['default'];
        if (is_numeric($finder->getFilterValue())) {
            // convert numbers
            $floatValue = floatval($finder->getFilterValue());
            if ($finder->getFilterValue() === $floatValue.'') {
                // dumb check to allow users search with strings like '001' without catching it as a number
                $value = $floatValue;
            }
        }

        if (null !== $value) {
            $queryBuilder->andWhere("{$finder->getQueryPath()} = :{$finder->getAlias()}");
            $queryBuilder->setParameter($finder->getAlias(), $value);
        }
    }
}
