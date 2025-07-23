<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\PublicType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\EvaluationBundle\Entity\Sequence\Sequence;
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
            ->add('public', PublicType::class)
            ->add('createdAt', DateType::class)
            ->add('updatedAt', DateType::class)
            ->add('creator', CreatorType::class)
            ->add('workspace', RelatedEntityType::class)
            // for evaluations
            ->add('required', BooleanType::class)
            ->add('evaluated', BooleanType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
