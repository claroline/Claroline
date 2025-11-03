<?php

namespace Icap\LessonBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TagType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Finder\ResourceNodeType;
use Doctrine\ORM\QueryBuilder;
use Icap\LessonBundle\Entity\Chapter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapter::class,
            'fulltext' => ['title', 'text'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class)
            ->add('text', TextType::class)
            ->add('internalNote', TextType::class)
            ->add('creator', CreatorType::class)
            ->add('createdAt', DateType::class)
            ->add('updatedAt', DateType::class)
            ->add('published', BooleanType::class)
            ->add('resourceNode', ResourceNodeType::class, [
                'joinQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin($alias.'.lesson', $alias.'_lesson');
                    $queryBuilder->leftJoin($alias.'_lesson.resourceNode', $finder->getAlias());
                },
            ])
            ->add('tags', TagType::class)
        ;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        // always exclude the root chapters from the results
        $queryBuilder->andWhere("{$finder->getQueryPath()}.parent IS NOT NULL");
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
