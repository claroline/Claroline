<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\HiddenType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Entity\Resource\ResourceEvaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
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
            ->add('status', ChoiceType::class, [
                'choices' => EvaluationStatus::all(),
            ])
            ->add('name', TextType::class)
            ->add('code', TextType::class)
            ->add('description', TextType::class)
            ->add('published', BooleanType::class)
            ->add('hidden', HiddenType::class)
            ->add('creator', CreatorType::class)
            ->add('parent', RelatedEntityType::class)
            ->add('workspace', RelatedEntityType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
