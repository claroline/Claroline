<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\CommunityBundle\Finder\UserType;
use Doctrine\ORM\QueryBuilder;

class ViewerType extends AbstractType
{
    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserType::class)
            ->add('seenAt', DateType::class)
            ->add('count', NumericType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy("{$finder->getQueryPath()}.seenAt", $finder->getSortValue());
        }
    }
}
