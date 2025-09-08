<?php

namespace Claroline\AgendaBundle\Finder;

use Claroline\AgendaBundle\Entity\Task;
use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\CoreBundle\Finder\PlannedObjectType;
use Claroline\CoreBundle\Finder\WorkspaceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
            'fulltext' => ['name', 'description'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plannedObject', PlannedObjectType::class)
            ->add('workspace', WorkspaceType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
