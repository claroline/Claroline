<?php

namespace Claroline\OpenBadgeBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TagType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CommunityBundle\Finder\OrganizationType;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadgeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BadgeClass::class,
            'fulltext' => ['name', 'description'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('archived', BooleanType::class, ['default' => false])
            ->add('issuer', RelatedEntityType::class)
            ->add('organizations', OrganizationType::class, ['nullable' => true])
            ->add('workspace', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin($alias.'.workspace', $finder->getAlias());
                    if (!$finder->hasFilter()) {
                        $queryBuilder->andWhere("({$finder->getAlias()}.id IS NULL OR ({$finder->getAlias()}.model = false AND {$finder->getAlias()}.archived = false))");
                    } else {
                        $filterValue = !is_array($finder->getFilterValue()) ? [$finder->getFilterValue()] : $finder->getFilterValue();
                        if (1 === count($filterValue)) {
                            $queryBuilder->andWhere("{$finder->getAlias()}.uuid = :workspace");
                            $queryBuilder->setParameter('workspace', $filterValue[0]);
                        } else {
                            $queryBuilder->andWhere("{$finder->getAlias()}.uuid IN (:workspace)");
                            $queryBuilder->setParameter('workspace', $filterValue);
                        }
                    }
                },
            ])
            ->add('tags', TagType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
