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

        $resolver->setAllowedTypes('default', ['null', 'scalar', 'array']);
        $resolver->define('choices');
        $resolver->setAllowedTypes('choices', ['array']);
        $resolver->setRequired('choices');
    }

    public function submit(mixed $filterValue, array $options): mixed
    {
        $requestValue = $options['default'];
        if (null !== $filterValue) {
            $requestValue = $filterValue;
        }

        if (is_array($requestValue) && 1 === count($requestValue)) {
            $requestValue = $requestValue[0];
        }

        return $requestValue;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy($finder->getQueryPath(), $finder->getSortValue());
        }

        if ($finder->hasFilter()) {
            $value = $finder->getFilterValue();
            if (is_array($value)) {
                $queryBuilder->andWhere("{$finder->getQueryPath()} IN (:{$finder->getAlias()})");
            } else {
                $queryBuilder->andWhere("{$finder->getQueryPath()} = :{$finder->getAlias()}");
            }

            $queryBuilder->setParameter($finder->getAlias(), $value);
        }
    }
}
