<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;

class RelatedEntityType extends AbstractType
{
    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if (null !== $finder->getFilterValue()) {
            $queryBuilder->join($finder->getQueryPath(false), $finder->getAlias());
            if (is_array($finder->getFilterValue())) {
                $queryBuilder->andWhere("{$finder->getAlias()}.uuid = :{$finder->getAlias()}");
            } else {
                $queryBuilder->andWhere("{$finder->getAlias()}.uuid IN (:{$finder->getAlias()})");
            }

            $queryBuilder->setParameter($finder->getAlias(), $finder->getFilterValue());
        }
    }
}
