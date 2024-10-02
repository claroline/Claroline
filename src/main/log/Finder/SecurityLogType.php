<?php

namespace Claroline\LogBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\CommunityBundle\Finder\UserType;
use Claroline\LogBundle\Entity\SecurityLog;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecurityLogType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SecurityLog::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('target', UserType::class)
        ;
    }

    public function getParent(): ?string
    {
        return LogType::class;
    }
}
