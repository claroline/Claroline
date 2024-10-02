<?php

namespace Claroline\LogBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\CoreBundle\Finder\ResourceNodeType;
use Claroline\CoreBundle\Finder\WorkspaceType;
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
            ->add('workspace', WorkspaceType::class)
            ->add('resource', ResourceNodeType::class)
        ;
    }

    public function getParent(): ?string
    {
        return LogType::class;
    }
}
