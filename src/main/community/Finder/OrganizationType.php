<?php

namespace Claroline\CommunityBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\PublicType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\PlatformRoles;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class OrganizationType extends AbstractType
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly ObjectManager $om
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Organization::class,
            'fulltext' => ['name', 'code', 'description'],
        ]);
    }

    public function submit(mixed $filterValue, array $options): ?array
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        $includedOrganizations = [];
        $excludedOrganizations = [];
        if (!empty($filterValue)) {
            $value = is_array($filterValue) ? $filterValue : [$filterValue];
            foreach ($value as $organization) {
                if (str_starts_with($organization, 'not:')) {
                    $excludedOrganizations[] = str_replace('not:', '', $organization);
                } else {
                    $includedOrganizations[] = $organization;
                }
            }
        }

        if ($this->tokenStorage->getToken() && $user instanceof User) {
            // we need to filter the results by the organizations owned by the current user
            // when used in another finder we will filter by the main organization of the user if no search is defined
            if (!empty($includedOrganizations)) {
                // there is a user search on organizations
                // we need to only keep the organizations owned by the current user
                if (!in_array(PlatformRoles::ADMIN, $this->tokenStorage->getToken()->getRoleNames())) {
                    $includedOrganizations = array_map(function (Organization $organization) {
                        return $organization->getUuid();
                    }, array_filter($user->getOrganizations(), function (Organization $organization) use ($includedOrganizations) {
                        return in_array($organization->getUuid(), $includedOrganizations);
                    }));
                }
            } else {
                // default behavior: only return results for the current user organization
                $includedOrganizations = [$user->getMainOrganization()->getUuid()];
            }
        }

        if (!empty($includedOrganizations) || !empty($excludedOrganizations)) {
            return [
                'included' => $includedOrganizations,
                'excluded' => $excludedOrganizations,
            ];
        }

        return null;
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('code', TextType::class)
            ->add('description', TextType::class)
            ->add('public', PublicType::class)
        ;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        $includedOrganizations = [];
        $excludedOrganizations = [];
        if (!empty($finder->getFilterValue())) {
            $value = $finder->getFilterValue();
            $includedOrganizations = $value['included'];
            $excludedOrganizations = $value['excluded'];
        }

        $user = $this->tokenStorage->getToken()?->getUser();
        if ($finder->isRoot() && $user instanceof User) {
            if (!in_array(PlatformRoles::ADMIN, $this->tokenStorage->getToken()->getRoleNames())) {
                $includedOrganizations = array_map(function (Organization $organization) {
                    return $organization->getUuid();
                }, $user->getOrganizations());
            } else {
                $includedOrganizations = [];
            }
        }

        if (!empty($includedOrganizations) || !empty($excludedOrganizations)) {
            if (1 === count($includedOrganizations)) {
                $queryBuilder->andWhere("{$finder->getAlias()}.uuid = :{$finder->getAlias()}");
                $queryBuilder->setParameter($finder->getAlias(), $includedOrganizations[0]);
                $finder->distinct(false);
            } else {
                if (!empty($includedOrganizations)) {
                    $queryBuilder->andWhere("{$finder->getAlias()}.uuid IN (:{$finder->getAlias()}Include)");
                    $queryBuilder->setParameter($finder->getAlias().'Include', $includedOrganizations);
                }

                if (!$finder->isRoot() && !empty($excludedOrganizations)) {
                    $parent = $finder->getParent();
                    $parentOptions = $finder->getParent()->getOptions();
                    $parentAlias = $parent->getAlias().'2';
                    $notExistAlias = str_replace($parent->getAlias(), $parentAlias, $finder->getAlias()).'2';

                    $notExistBuilder = $this->om->createQueryBuilder()
                        ->select($parentAlias.'.'.$parentOptions['identifier'])
                        ->from($parentOptions['data_class'], $parentAlias);

                    if (isset($options['joinQuery'])) {
                        $options['joinQuery']($notExistBuilder, $finder, $options);

                        // rewrite custom joinQuery to change the aliases.
                        // this is complexe and far from perfect (will not join condition if any).
                        // it needs to be managed in another way
                        $joinPart = $notExistBuilder->getDQLPart('join');
                        $notExistBuilder->resetDQLPart('join');

                        $toReplace = [
                            $finder->getAlias() => $notExistAlias,
                            $parent->getAlias() => $parentAlias,
                        ];

                        foreach ($joinPart as $rootJoins) {
                            foreach ($rootJoins as $joinCondition) {
                                $toReplace[$joinCondition->getAlias()] = $joinCondition->getAlias().'2';
                            }
                        }

                        foreach ($joinPart as $rootAlias => $rootJoins) {
                            foreach ($rootJoins as $joinCondition) {
                                $join = $joinCondition->getJoin();
                                $joinAlias = $joinCondition->getAlias();
                                foreach ($toReplace as $alias => $replace) {
                                    $join = str_replace($alias, $replace, $join);
                                    $joinAlias = str_replace($alias, $replace, $joinAlias);
                                }

                                $notExistBuilder->add('join', [$rootAlias => new Join($joinCondition->getJoinType(), $join, $joinAlias, $joinCondition->getCondition())], true);
                            }
                        }
                    } else {
                        $notExistBuilder->leftJoin($finder->getQueryPath(false), $notExistAlias);
                    }

                    $notExistBuilder
                        ->where("{$parent->getAlias()}.{$parentOptions['identifier']} = $parentAlias.{$parentOptions['identifier']}")
                        ->andWhere("$notExistAlias.{$options['identifier']} IN (:{$finder->getAlias()}Exclude)");

                    $queryBuilder->andWhere("NOT EXISTS ({$notExistBuilder->getDQL()})");
                    $queryBuilder->setParameter($finder->getAlias().'Exclude', $excludedOrganizations);
                }
            }
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
