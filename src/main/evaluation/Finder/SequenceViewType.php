<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\ViewerType;
use Claroline\EvaluationBundle\Entity\Sequence\SequenceView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SequenceViewType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SequenceView::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sequence', RelatedEntityType::class)
        ;
    }

    public function getParent(): ?string
    {
        return ViewerType::class;
    }
}
