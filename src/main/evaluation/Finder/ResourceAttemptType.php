<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\CommunityBundle\Finder\UserType;
use Claroline\CoreBundle\Finder\ResourceNodeType;
use Claroline\EvaluationBundle\Entity\UserEvaluation\ResourceAttempt;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceAttemptType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResourceAttempt::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('resourceNode', ResourceNodeType::class, [
                'joinQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $usedAliases = $queryBuilder->getAllAliases();
                    if (!in_array('resource_user_evaluation', $usedAliases)) {
                        $queryBuilder->leftJoin("{$alias}.resourceUserEvaluation", 'resource_user_evaluation');
                    }

                    $queryBuilder->leftJoin('resource_user_evaluation.resourceNode', $finder->getAlias());
                },
            ])
            ->add('user', UserType::class, [
                'joinQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $usedAliases = $queryBuilder->getAllAliases();
                    if (!in_array('resource_user_evaluation', $usedAliases)) {
                        $queryBuilder->leftJoin("{$alias}.resourceUserEvaluation", 'resource_user_evaluation');
                    }

                    $queryBuilder->leftJoin('resource_user_evaluation.user', $finder->getAlias());
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return EvaluationType::class;
    }
}
