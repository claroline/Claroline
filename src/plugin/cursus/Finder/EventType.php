<?php

namespace Claroline\CursusBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Finder\PlannedObjectType;
use Claroline\CursusBundle\Entity\Event;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\EventUser;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'fulltext' => ['code'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class)
            ->add('plannedObject', PlannedObjectType::class)
            ->add('session', SessionType::class)
            ->add('workspace', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null === $finder->getFilterValue()) {
                        return;
                    }

                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin($alias.'.session', 'sessionWorkspace');
                    $queryBuilder->leftJoin('sessionWorkspace.workspace', $finder->getAlias());
                    $queryBuilder->andWhere("{$finder->getAlias()}.uuid = :workspaceId");
                    $queryBuilder->setParameter('workspaceId', $finder->getFilterValue());
                },
            ])
            ->add('user', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null === $finder->getFilterValue()) {
                        return;
                    }

                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin(EventUser::class, 'eu', Join::WITH, 'eu.event = '.$alias);
                    $queryBuilder->leftJoin('eu.user', 'u');
                    $queryBuilder->andWhere('eu.confirmed = 1 AND eu.validated = 1');
                    $queryBuilder->andWhere('u.uuid = :userId');
                    $queryBuilder->setParameter('userId', $finder->getFilterValue());
                },
            ])
            ->add('capacity', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null === $finder->getFilterValue()) {
                        return;
                    }

                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $countSubquery = "(
                        SELECT COUNT(DISTINCT su)
                        FROM Claroline\CursusBundle\Entity\Registration\EventUser AS su
                        LEFT JOIN su.user AS u
                        WHERE su.type = :registrationType
                          AND su.event = $alias
                          AND (su.confirmed = 1 AND su.validated = 1)
                          AND u.disabled = false AND u.isRemoved = false AND u.technical = false
                    )";

                    switch ($finder->getFilterValue()) {
                        case 'available_seats':
                            $queryBuilder->andWhere("($alias.maxUsers IS NULL OR $alias.maxUsers > $countSubquery)");
                            break;
                        case 'full':
                            $queryBuilder->andWhere("($alias.maxUsers IS NOT NULL AND $alias.maxUsers = $countSubquery)");
                            break;
                        case 'missing_seats':
                            $queryBuilder->andWhere("($alias.maxUsers IS NOT NULL AND $alias.maxUsers < $countSubquery)");
                            break;
                    }

                    $queryBuilder->setParameter('registrationType', AbstractRegistration::LEARNER);
                },
            ])
        ;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy("{$finder->getQueryPath()}_plannedObject.name", $finder->getSortValue());
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
