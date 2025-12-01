<?php

namespace Claroline\CursusBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\HiddenType;
use Claroline\AppBundle\API\Finder\Type\NumericType;
use Claroline\AppBundle\API\Finder\Type\PeriodStatusType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CursusBundle\Entity\Registration\AbstractRegistration;
use Claroline\CursusBundle\Entity\Registration\SessionUser;
use Claroline\CursusBundle\Entity\Session;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SessionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Session::class,
            'fulltext' => ['code'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class)
            ->add('hidden', HiddenType::class)
            ->add('startDate', DateType::class)
            ->add('endDate', DateType::class)
            ->add('status', PeriodStatusType::class)
            ->add('price', NumericType::class)
            ->add('canceled', BooleanType::class, ['default' => false])
            ->add('course', CourseType::class)
            ->add('workspace', RelatedEntityType::class)
            ->add('location', RelatedEntityType::class)
            ->add('creator', CreatorType::class)
            ->add('createdAt', DateType::class)
            ->add('updatedAt', DateType::class)
            ->add('user', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null === $finder->getFilterValue()) {
                        return;
                    }

                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin(SessionUser::class, 'su', Join::WITH, 'su.session = '.$alias);
                    $queryBuilder->leftJoin('su.user', 'u');
                    $queryBuilder->andWhere('su.confirmed = 1 AND su.validated = 1');
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
                        FROM Claroline\CursusBundle\Entity\Registration\SessionUser AS su
                        LEFT JOIN su.user AS u
                        WHERE su.type = :registrationType
                          AND su.session = $alias
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
            $queryBuilder->addOrderBy("{$finder->getQueryPath()}.startDate", $finder->getSortValue());
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
