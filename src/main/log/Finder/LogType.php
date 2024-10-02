<?php

namespace Claroline\LogBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CommunityBundle\Finder\UserType;

class LogType extends AbstractType
{
    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('event', TextType::class)
            ->add('date', DateType::class)
            ->add('doer', UserType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
