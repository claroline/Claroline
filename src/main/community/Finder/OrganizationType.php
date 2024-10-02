<?php

namespace Claroline\CommunityBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\PublicType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrganizationType extends AbstractType
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organization::class,
            'fulltext' => ['name', 'code', 'description'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('code', TextType::class)
            ->add('description', TextType::class)
            ->add('public', PublicType::class);
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        $organizations = [];
        if ($this->tokenStorage->getToken() && $this->tokenStorage->getToken()?->getUser() instanceof User) {
            $user = $this->tokenStorage->getToken()?->getUser();
            if ($finder->isRoot()) {
                if (!in_array(PlatformRoles::ADMIN, $this->tokenStorage->getToken()->getRoleNames())) {
                    $organizations = $user->getOrganizations();
                }
            } else {
                $organizations = [$user->getMainOrganization()];
            }
        }

        if (!empty($organizations)) {
            if (1 === count($organizations)) {
                $queryBuilder->andWhere("{$finder->getAlias()} = :{$finder->getAlias()}");
                $queryBuilder->setParameter($finder->getAlias(), $organizations[0]);
                $finder->distinct(false);
            } else {
                $queryBuilder->andWhere("{$finder->getAlias()} IN (:{$finder->getAlias()})");
                $queryBuilder->setParameter($finder->getAlias(), $organizations);
            }
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
