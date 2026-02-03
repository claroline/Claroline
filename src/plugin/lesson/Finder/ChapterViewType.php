<?php

namespace Icap\LessonBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\ViewerType;
use Icap\LessonBundle\Entity\ChapterView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChapterViewType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChapterView::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('chapter', RelatedEntityType::class)
        ;
    }

    public function getParent(): ?string
    {
        return ViewerType::class;
    }
}
