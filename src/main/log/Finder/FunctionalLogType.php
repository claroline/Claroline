<?php

namespace Claroline\LogBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\LogBundle\Entity\FunctionalLog;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FunctionalLogType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FunctionalLog::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('objectClass', TextType::class, ['mode' => TextType::MODE_EXACT])
            ->add('objectId', TextType::class, ['mode' => TextType::MODE_EXACT])
            ->add('contextId', TextType::class, ['mode' => TextType::MODE_EXACT])
        ;
    }

    public function getParent(): ?string
    {
        return LogType::class;
    }
}
