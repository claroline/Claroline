<?php

namespace Claroline\CoreBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\FinderInterface;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\PeriodStatusType;
use Claroline\AppBundle\API\Finder\Type\RelatedEntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CoreBundle\Entity\Planning\PlannedObject;
use Claroline\CoreBundle\Entity\Planning\Planning;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
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
            ->add('status', PeriodStatusType::class)
            ->add('startDate', DateType::class)
            ->add('endDate', DateType::class)
            ->add('location', RelatedEntityType::class)
            ->add('planning', EntityType::class, [
                'data_class' => Planning::class,
                'identifier' => 'objectId',
                'joinQuery' => static function (QueryBuilder $queryBuilder, FinderInterface $finder): void {
                    $alias = $finder->getAlias();
                    if (!$finder->isRoot()) {
                        $alias = $finder->getParent()->getAlias();
                    }

                    $queryBuilder->leftJoin(Planning::class, $finder->getAlias(), Join::WITH, "$alias MEMBER OF {$finder->getAlias()}.plannedObjects");
                },
            ])
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
