<?php

namespace Claroline\AnnouncementBundle\Finder;

use Claroline\AnnouncementBundle\Entity\AnnouncementView;
use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\ViewerType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementViewType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AnnouncementView::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('announcement', RelatedEntityType::class)
        ;
    }

    public function getParent(): ?string
    {
        return ViewerType::class;
    }
}
