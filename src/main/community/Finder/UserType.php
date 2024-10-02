<?php

namespace Claroline\CommunityBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
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
            //->add('groups', GroupType::class)
            ->add('roles', RoleType::class, [
                /*'joinQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $queryBuilder->leftJoin(Role::class, $finder->getAlias(), Join::WITH, "$alias MEMBER OF {$finder->getAlias()}.users");
                },*/
            ])
            ->add('teams', TeamType::class, [
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

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
