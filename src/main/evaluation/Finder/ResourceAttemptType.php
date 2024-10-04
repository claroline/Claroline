<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceAttemptType extends AbstractType
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
            ->add('user', RelatedEntityType::class)
            ->add('resourceNode', RelatedEntityType::class)
            ->add('workspace', RelatedEntityType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EvaluationType::class;
    }
}
