<?php

namespace Claroline\OpenBadgeBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\DateType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\CommunityBundle\Finder\UserType;
use Claroline\OpenBadgeBundle\Entity\Assertion;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssertionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Assertion::class,
            'fulltext' => [],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('issuedOn', DateType::class)
            ->add('revoked', BooleanType::class, [
                'default' => false,
            ])
            ->add('recipient', UserType::class)
            ->add('badge', BadgeType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
