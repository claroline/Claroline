<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\PublicType;
use Claroline\AppBundle\API\Finder\Type\TagType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Finder\WorkspaceType;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SequenceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sequence::class,
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
            ->add('archived', BooleanType::class, ['default' => false])
            ->add('public', PublicType::class)
            ->add('createdAt', DateType::class)
            ->add('updatedAt', DateType::class)
            ->add('creator', CreatorType::class)
            ->add('workspace', WorkspaceType::class)
            ->add('tags', TagType::class)
            ->add('roles', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null !== $finder->getFilterValue()) {
                        $alias = $finder->getAlias();
                        if (!$finder->isRoot()) {
                            $alias = $finder->getParent()->getAlias();
                        }

                        $queryBuilder->leftJoin($alias.'.assignments', 'assignments');
                        $queryBuilder->leftJoin('assignments.role', 'roles');
                        $queryBuilder->andWhere("($alias.public = true OR roles.name IN (:roles))");
                        $queryBuilder->setParameter('roles', $finder->getFilterValue());
                    }
                },
            ])
            // for evaluations (deprecated)
            ->add('required', BooleanType::class)
            ->add('evaluated', BooleanType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }

    public function buildQuery(QueryBuilder $queryBuilder, FinderInterface $finder, array $options): void
    {
        if ($finder->getSortValue()) {
            $queryBuilder->addOrderBy("{$finder->getQueryPath()}.name", $finder->getSortValue());
        }
    }
}
