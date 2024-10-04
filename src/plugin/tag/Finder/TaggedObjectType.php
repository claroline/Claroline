<?php

namespace Claroline\TagBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\TagBundle\Entity\TaggedObject;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TaggedObjectType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaggedObject::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('tag', TagType::class)
            ->add('objectClass', TextType::class, ['mode' => TextType::MODE_EXACT])
            ->add('objectId', TextType::class, ['mode' => TextType::MODE_EXACT])
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
