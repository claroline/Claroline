<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        // the entity classname managed by the finder
        $resolver
            ->define('data_class')
            ->required();

        $resolver
            ->define('identifier')
            ->default('uuid')
            ->required();

        // enabled multi-column search for the entity
        $resolver
            ->define('fulltext')
            ->allowedTypes('null', 'array')
            ->default([]);

        // allows customizing the join to the entity when the finder is embedded into another
        // the callback is called with the QueryBuilder, FinderInterface and resolved options as parameters.
        $resolver
            ->define('joinQuery')
            ->allowedTypes('callable');
        $resolver
            ->define('nullable')
            ->allowedTypes('boolean')
            ->default(false);
    }

    public function submit(mixed $filterValue, array $options): ?array
    {
        if (empty($filterValue)) {
            return null;
        }

        $requestValue = is_array($filterValue) ? $filterValue : [$filterValue];

        $value = [];
        foreach ($requestValue as $item) {
            if ($item instanceof $options['data_class']) {
                $identifierGetter = 'get'.ucwords($options['identifier']);
                if (method_exists($item, $identifierGetter)) {
                    $value[] = $item->{$identifierGetter}();
                }
            } else {
                $value[] = $item;
            }
        }

        return $value;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if (!$finder->isRoot()) {
            if (isset($options['joinQuery'])) {
                $options['joinQuery']($queryBuilder, $finder, $options);
            } else {
                $queryBuilder->leftJoin($finder->getQueryPath(false), $finder->getAlias());
            }

            $finder->distinct();

            if ($finder->hasFilter()) {
                $nullableCondition = '';
                if ($options['nullable']) {
                    $nullableCondition = "{$finder->getAlias()}.{$options['identifier']} IS NULL OR";
                }

                if (1 === count($finder->getFilterValue())) {
                    $queryBuilder
                        ->andWhere("($nullableCondition {$finder->getAlias()}.{$options['identifier']} = :{$finder->getAlias()})")
                        ->setParameter($finder->getAlias(), $finder->getFilterValue()[0]);
                    $finder->distinct(false);
                } else {
                    $queryBuilder
                        ->andWhere("($nullableCondition {$finder->getAlias()}.{$options['identifier']} IN (:{$finder->getAlias()}))")
                        ->setParameter($finder->getAlias(), $finder->getFilterValue());
                }
            }
        }

        if ($finder->isRoot() && !$finder->hasFilter() && !empty($options['fulltext']) && !empty($finder->getSearchValue())) {
            $fulltextQuery = [];
            foreach ($options['fulltext'] as $propName) {
                $fulltextQuery[] = "LOWER({$finder->getQueryPath()}.$propName) LIKE :{$finder->getAlias()}Fulltext";
            }
            $queryBuilder->andWhere('('.implode(' OR ', $fulltextQuery).')');
            $queryBuilder->setParameter($finder->getAlias().'Fulltext', '%'.addcslashes(strtolower($finder->getSearchValue()), '%_').'%');
        }
    }
}
