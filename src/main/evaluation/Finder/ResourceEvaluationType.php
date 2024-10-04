<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\NumericType;
use Claroline\CommunityBundle\Finder\UserType;
use Claroline\CoreBundle\Entity\Resource\ResourceUserEvaluation;
use Claroline\CoreBundle\Finder\ResourceNodeType;
use Claroline\CoreBundle\Finder\WorkspaceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceEvaluationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResourceUserEvaluation::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nbAttempts', NumericType::class)
            ->add('nbOpenings', NumericType::class)
            ->add('user', UserType::class)
            ->add('resourceNode', ResourceNodeType::class)
            //->add('workspace', WorkspaceType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EvaluationType::class;
    }
}
