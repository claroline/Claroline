<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\ClosureType;
use Claroline\AppBundle\API\Finder\Type\NumericType;
use Claroline\CommunityBundle\Finder\UserType;
use Claroline\CoreBundle\Finder\ResourceNodeType;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceEvaluation;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceEvaluationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResourceEvaluation::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbAttempts', NumericType::class)
            ->add('resourceNode', ResourceNodeType::class)
            ->add('user', UserType::class)
            ->add('sequence', ClosureType::class, [
                'buildQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    if (null !== $finder->getFilterValue()) {
                        $alias = $finder->getAlias();
                        if (!$finder->isRoot()) {
                            $alias = $finder->getParent()->getAlias();
                        }

                        $queryBuilder->leftJoin("{$finder->getParent()->getAlias()}.resourceNode", 'er');

                        $queryBuilder->andWhere("EXISTS (
                            SELECT s.id
                            FROM Claroline\EvaluationBundle\Entity\Sequence\Step AS s
                            LEFT JOIN s.path AS p
                            LEFT JOIN s.resource AS r
                            WHERE r = er
                              AND (s.required = 1 OR s.scored = 1)
                              AND p.uuid = :$alias
                        )");

                        $queryBuilder->setParameter($alias, $finder->getFilterValue());
                    }
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return EvaluationType::class;
    }
}
