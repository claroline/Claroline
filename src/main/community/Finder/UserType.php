<?php

namespace Claroline\CommunityBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CommunityBundle\Entity\Team;
use Claroline\CoreBundle\Entity\User;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'fulltext' => ['username', 'firstName', 'lastName', 'email'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('email', TextType::class)
            ->add('lastActivity', DateType::class)
            ->add('created', DateType::class)
            ->add('disabled', BooleanType::class, ['default' => false])
            ->add('groups', RelatedEntityType::class)
            ->add('roles', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null === $finder->getFilterValue()) {
                        return;
                    }

                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->andWhere("EXISTS (
                        SELECT r.id
                        FROM Claroline\CoreBundle\Entity\Role AS r
                        LEFT JOIN Claroline\CoreBundle\Entity\User AS ur WITH (ur.id = $alias.id AND r MEMBER OF ur.roles)
                        LEFT JOIN Claroline\CoreBundle\Entity\Group AS gr WITH (r MEMBER OF gr.roles AND gr MEMBER OF $alias.groups)
                        WHERE r.uuid IN (:roles)
                          AND (ur IS NOT NULL OR gr IS NOT NULL)
                    )");

                    $filterValue = is_array($finder->getFilterValue()) ? $finder->getFilterValue() : [$finder->getFilterValue()];
                    $queryBuilder->setParameter('roles', $filterValue);
                },
            ])
            ->add('workspace', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null === $finder->getFilterValue()) {
                        return;
                    }

                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->andWhere("EXISTS (
                        SELECT wsr.id
                        FROM Claroline\CoreBundle\Entity\Role AS wsr
                        LEFT JOIN Claroline\CoreBundle\Entity\User AS wsru WITH (wsru.id = $alias.id AND wsr MEMBER OF wsru.roles)
                        LEFT JOIN Claroline\CoreBundle\Entity\Group AS wsrg WITH (wsr MEMBER OF wsrg.roles AND wsrg MEMBER OF $alias.groups)
                        LEFT JOIN wsr.workspace AS wsrw
                        WHERE wsrw.uuid = :workspace
                          AND (wsru IS NOT NULL OR wsrg IS NOT NULL)
                    )");
                    $queryBuilder->setParameter('workspace', $finder->getFilterValue());
                },
            ])
            ->add('teams', RelatedEntityType::class, [
                'joinQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin(Team::class, $finder->getAlias(), Join::WITH, "$alias MEMBER OF {$finder->getAlias()}.users");
                },
            ])
            ->add('organizations', OrganizationType::class, [
                'fulltext' => null,
                'joinQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin($alias.'.userOrganizationReferences', $alias.'_ref');
                    $queryBuilder->leftJoin($alias.'_ref.organization', $finder->getAlias());
                },
            ])
        ;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy("{$finder->getQueryPath()}.lastName", $finder->getSortValue());
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
