<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;

class UuidType extends AbstractType
{
    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy($finder->getQueryPath(), $finder->getSortValue());
        }

        $alias = $finder->getAlias();
        if (!$finder->isRoot()) {
            $alias = $finder->getParent()->getAlias();
        }

        if (null !== $finder->getFilterValue()) {
            $queryBuilder->andWhere("{$alias}.uuid = :{$finder->getAlias()}");
            $queryBuilder->setParameter($finder->getAlias(), $finder->getFilterValue());
        }
    }
}
