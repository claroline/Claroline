<?php

namespace Claroline\CoreBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\FinderQuery;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\HiddenType;
use Claroline\AppBundle\API\Finder\Type\PublicType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CommunityBundle\Finder\OrganizationType;
use Claroline\CommunityBundle\Finder\RoleType;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class WorkspaceType extends AbstractType
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workspace::class,
            'fulltext' => ['name', 'code', 'description'],
        ]);
    }

    public function resolveFilters(FinderQuery $query)
    {
        if ($query->hasFilter('administrated')) {

        }
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('code', TextType::class)
            ->add('description', TextType::class)
            ->add('model', BooleanType::class, ['default' => false])
            ->add('personal', BooleanType::class, ['default' => false])
            ->add('hidden', HiddenType::class)
            ->add('archived', BooleanType::class, ['default' => false])
            ->add('public', PublicType::class)
            ->add('organizations', OrganizationType::class)
            ->add('creator', CreatorType::class)
            ->add('roles', RoleType::class)
            ->add('administrated', ClosureType::class, [
                'buildQuery' => function (QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void {
                    if (in_array(PlatformRoles::ADMIN, $this->tokenStorage->getToken()->getRoleNames())) {
                        return;
                    }

                    if ($finder->getFilterValue()) {
                        $queryBuilder->andWhere("({$finder->getParent()->getQueryPath()}.creator = :creatorCurrentUser)");
                        $queryBuilder->setParameter('creatorCurrentUser', $this->tokenStorage->getToken()->getUser());

                        /*$queryBuilder->andWhere("({$finder->getParent()->getQueryPath()}.organizations IN (:administratedOrganizations))");
                        $queryBuilder->setParameter('administratedOrganizations', []);*/
                    }
                }
            ])
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
