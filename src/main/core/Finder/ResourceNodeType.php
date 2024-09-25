<?php

namespace Claroline\CoreBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\HiddenType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceNodeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResourceNode::class,
            'fulltext' => ['name', 'code', 'description'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('code', TextType::class)
            ->add('description', TextType::class)
            ->add('published', BooleanType::class)
            ->add('hidden', HiddenType::class)
            ->add('creator', CreatorType::class)
            ->add('parent', RelatedEntityType::class)
            ->add('workspace', RelatedEntityType::class)
            ->add('resourceType', ClosureType::class, [
                'buildQuery' => function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null !== $finder->getFilterValue()) {
                        $queryBuilder->join($finder->getQueryPath(), 'ort');
                        if (is_array($finder->getFilterValue())) {
                            $queryBuilder->andWhere('ort.name IN (:resourceType)');
                        } else {
                            $queryBuilder->andWhere('ort.name = :resourceType');
                        }
                        $queryBuilder->setParameter('resourceType', $finder->getFilterValue());
                    }
                },
            ])
            // ->add('roles', RoleType::class)

            /*->add('personal', BooleanType::class, ['default' => false])
            ->add('hidden', HiddenType::class)
            ->add('archived', BooleanType::class, ['default' => false])
            ->add('public', PublicType::class)
            ->add('organizations', OrganizationType::class)*/
        ;

        /*$qb->leftJoin('obj.rights', 'rights');
        $qb->join('rights.role', 'rightsr');
        $qb->andWhere('rightsr.name IN (:roles)');
        $qb->andWhere('BIT_AND(rights.mask, 1) = 1');
        $qb->setParameter('roles', $filterValue);*/
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
