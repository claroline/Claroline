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
use Claroline\CoreBundle\Entity\Role;
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
            'disabled' => false,
            'fulltext' => ['username', 'firstName', 'lastName', 'email'],
        ]);

        $resolver->setAllowedValues('disabled', [null, true, false]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class)
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->add('email', TextType::class)
            ->add('lastActivity', DateType::class)
            ->add('createdAt', DateType::class)
            ->add('updatedAt', DateType::class)
            ->add('disabled', BooleanType::class, ['default' => $options['disabled']])
            ->add('groups', RelatedEntityType::class)
            ->add('roles', RelatedEntityType::class, [
                'joinQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin(Role::class, $finder->getAlias(), Join::WITH, "$alias MEMBER OF {$finder->getAlias()}.users");
                },
            ])
            ->add('workspace', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null !== $finder->getFilterValue()) {
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
                    }
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
