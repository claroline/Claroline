<?php

namespace Claroline\CommunityBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RoleType extends AbstractType
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
            'fulltext' => ['name', 'translationKey', 'description'],
            'identifier' => 'name',
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('translationKey', TextType::class)
            ->add('description', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [Role::PLATFORM, Role::WORKSPACE, Role::USER],
            ])
        ;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        $roles = [];
        if (!$finder->isRoot() && null !== $finder->getFilterValue()) {
            $roles = $finder->getFilterValue();
        }

        // if not admin, only return the roles of the current user
        $tokenRoles = $this->tokenStorage->getToken()?->getRoleNames() ?? [PlatformRoles::ANONYMOUS];
        $isAdmin = $this->tokenStorage->getToken() && in_array(PlatformRoles::ADMIN, $tokenRoles);

        if (!empty($roles)) {
            $roleNames = $isAdmin ? $roles : array_intersect($roles, $tokenRoles);
            $queryBuilder->andWhere("{$finder->getQueryPath()}.name IN (:{$finder->getAlias()})");
            $queryBuilder->setParameter($finder->getAlias(), $roleNames);

            if (1 >= count($roleNames)) {
                $finder->distinct(false);
            }
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
