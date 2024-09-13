<?php

namespace Claroline\AppBundle\API\Finder\Type;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * A filter which check the status of a date period.
 */
class PeriodStatusType extends AbstractType
{
    public const NOT_STARTED = 'not_started';
    public const IN_PROGRESS = 'in_progress';
    public const ENDED = 'ended';
    public const NOT_ENDED = 'not_ended';

    public function configureOptions(OptionsResolver $resolver): void
    {
        // the default filter to apply if not defined by the FinderQuery
        $resolver
            ->define('default')
            ->default(null)
            ->allowedValues([self::NOT_STARTED, self::IN_PROGRESS, self::ENDED, self::NOT_ENDED]);

        // The name of the entity prop holding the start date of the period
        $resolver
            ->define('startPropName')
            ->default('startDate')
            ->required()
            ->allowedTypes('string');

        // The name of the entity prop holding the end date of the period
        $resolver
            ->define('endPropName')
            ->default('endDate')
            ->required()
            ->allowedTypes('string');
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        $value = $finder->getFilterValue() ? $finder->getFilterValue() : $options['default'];

        if (empty($value) || !in_array($value, [self::NOT_STARTED, self::IN_PROGRESS, self::ENDED, self::NOT_ENDED])) {
            return;
        }

        $alias = $finder->getAlias();
        if (!$finder->isRoot()) {
            $alias = $finder->getParent()->getQueryPath();
        }

        $startProp = $alias.'.'.$options['startPropName'];
        $endProp = $alias.'.'.$options['endPropName'];

        switch ($value) {
            case self::NOT_STARTED:
                $queryBuilder->andWhere("$startProp < :{$finder->getAlias()}Now");
                break;
            case self::IN_PROGRESS:
                $queryBuilder->andWhere("($startProp <= :{$finder->getAlias()}Now AND $endProp >= :{$finder->getAlias()}Now)");
                break;
            case self::ENDED:
                $queryBuilder->andWhere("$endProp < :{$finder->getAlias()}Now");
                break;
            case self::NOT_ENDED:
                $queryBuilder->andWhere("$endProp >= :{$finder->getAlias()}Now");
                break;
        }

        $queryBuilder->setParameter($finder->getAlias().'Now', new \DateTime());
    }
}
