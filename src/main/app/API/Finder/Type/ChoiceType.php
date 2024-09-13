<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'default' => null,
        ]);

        $resolver->setAllowedTypes('default', ['null', 'scalar']);
        $resolver->define('choices');
        $resolver->setAllowedTypes('choices', ['array']);
        $resolver->setRequired('choices');
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        $value = !empty($finder->getFilterValue()) ? $finder->getFilterValue() : $options['default'];
        if (null !== $value) {
            if (is_array($value) && 1 === count($value)) {
                $value = $value[0];
            }

            if (is_array($value)) {
                $queryBuilder->andWhere("{$finder->getQueryPath()} IN (:{$finder->getAlias()})");
            } else {
                $queryBuilder->andWhere("{$finder->getQueryPath()} = :{$finder->getAlias()}");
            }

            $queryBuilder->setParameter($finder->getAlias(), $value);
        }
    }
}
