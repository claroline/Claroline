<?php

namespace Claroline\EvaluationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\NumericType;
use Claroline\EvaluationBundle\Library\EvaluationStatus;

class EvaluationType extends AbstractType
{
    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ChoiceType::class, [
                'choices' => EvaluationStatus::all(),
            ])
            ->add('score', NumericType::class)
            ->add('progression', NumericType::class)
            ->add('date', DateType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
