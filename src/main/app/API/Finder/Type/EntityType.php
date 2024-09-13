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

        // enabled multi-column search for the entity
        $resolver
            ->define('fulltext')
            ->allowedTypes('null', 'array')
            ->default([]);

        // allows to customize to join the entity when the finder is embedded into another
        // the callback is called with the QueryBuilder, FinderInterface and resolved options as parameters.
        $resolver
            ->define('joinQuery')
            ->allowedTypes('callable');
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
        }

        // only enable fulltext search for first level finder for now
        if ($finder->isRoot() && !empty($options['fulltext']) && !empty($finder->getSearchValue())) {
            $fulltextQuery = [];
            foreach ($options['fulltext'] as $propName) {
                $fulltextQuery[] = "LOWER({$finder->getQueryPath()}.$propName) LIKE :{$finder->getAlias()}Fulltext";
            }
            $queryBuilder->andWhere('('.implode(' OR ', $fulltextQuery).')');
            $queryBuilder->setParameter($finder->getAlias().'Fulltext', '%'.addcslashes(strtolower($finder->getSearchValue()), '%_').'%');
        }
    }
}
