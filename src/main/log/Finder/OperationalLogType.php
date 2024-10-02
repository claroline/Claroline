<?php

namespace Claroline\LogBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\LogBundle\Entity\OperationalLog;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationalLogType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OperationalLog::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contextName', TextType::class, ['mode' => TextType::MODE_EXACT])
            ->add('contextId', TextType::class, ['mode' => TextType::MODE_EXACT])
            ->add('objectId', TextType::class, ['mode' => TextType::MODE_EXACT])
            ->add('objectClass', TextType::class, ['mode' => TextType::MODE_EXACT])
        ;
    }

    public function getParent(): ?string
    {
        return LogType::class;
    }
}
