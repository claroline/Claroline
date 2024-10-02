<?php

namespace Claroline\CoreBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Entity\Planning\PlannedObject;
use Claroline\CoreBundle\Entity\Planning\Planning;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlannedObjectType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PlannedObject::class,
            'fulltext' => ['name', 'description'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('type', TextType::class, ['mode' => TextType::MODE_EXACT])
            ->add('startDate', DateType::class)
            ->add('endDate', DateType::class)
            ->add('location', LocationType::class)
            ->add('planning', EntityType::class, [
                'data_class' => Planning::class,
                'identifier' => 'objectId',
            ])
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
