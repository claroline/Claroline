<?php

namespace Claroline\OpenBadgeBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CommunityBundle\Finder\OrganizationType;
use Claroline\CoreBundle\Finder\WorkspaceType;
use Claroline\OpenBadgeBundle\Entity\BadgeClass;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BadgeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => BadgeClass::class,
            'fulltext' => ['name', 'description'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->add('archived', BooleanType::class, ['default' => false])
            ->add('issuer', OrganizationType::class)
            ->add('workspace', WorkspaceType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
