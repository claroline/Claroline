<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\CommunityBundle\Finder\UserType;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\CoreBundle\Finder\WorkspaceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkspaceEvaluationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evaluation::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('workspace', WorkspaceType::class)
            ->add('user', UserType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EvaluationType::class;
    }
}
