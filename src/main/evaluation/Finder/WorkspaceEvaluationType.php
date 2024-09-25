<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\CommunityBundle\Finder\UserType;
use Claroline\CoreBundle\Entity\Workspace\Evaluation;
use Claroline\EvaluationBundle\Library\EvaluationStatus;
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
            ->add('status', ChoiceType::class, [
                'choices' => EvaluationStatus::all(),
            ])
            ->add('workspace', RelatedEntityType::class)
            ->add('user', UserType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
