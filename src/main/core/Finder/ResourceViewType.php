<?php

namespace Claroline\CoreBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\ViewerType;
use Claroline\CoreBundle\Entity\Resource\ResourceView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceViewType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ResourceView::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('resourceNode', RelatedEntityType::class)
        ;
    }

    public function getParent(): ?string
    {
        return ViewerType::class;
    }
}
