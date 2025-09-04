<?php

namespace Claroline\CoreBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\HiddenType;
use Claroline\AppBundle\API\Finder\Type\PublicType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TagType;
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
            ->add('public', PublicType::class)
            ->add('active', BooleanType::class, [
                'default' => true,
            ])
            ->add('hidden', HiddenType::class)
            ->add('creator', CreatorType::class)
            ->add('parent', RelatedEntityType::class)
            ->add('workspace', WorkspaceType::class)
            ->add('resourceType', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
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
            // for evaluations
            ->add('required', BooleanType::class)
            ->add('evaluated', BooleanType::class)
            ->add('creationDate', DateType::class)
            ->add('modificationDate', DateType::class)
            ->add('tags', TagType::class)
            // to remove with the path plugin
            ->add('deprecatedPath', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->andWhere("$alias.mimeType != 'custom/innova_path'");
                },
            ])
        ;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy("{$finder->getQueryPath()}.name", $finder->getSortValue());
        }
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
