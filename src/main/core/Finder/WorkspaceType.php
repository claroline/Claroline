<?php

namespace Claroline\CoreBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\BooleanType;
use Claroline\AppBundle\API\Finder\Type\CreatorType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\HiddenType;
use Claroline\AppBundle\API\Finder\Type\PublicType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\CommunityBundle\Finder\OrganizationType;
use Claroline\CommunityBundle\Finder\RoleType;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WorkspaceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Workspace::class,
            'fulltext' => ['name', 'code', 'description'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class)
            ->add('code', TextType::class)
            ->add('description', TextType::class)
            ->add('model', BooleanType::class, ['default' => false])
            ->add('personal', BooleanType::class, ['default' => false])
            ->add('hidden', HiddenType::class)
            ->add('archived', BooleanType::class, ['default' => false])
            ->add('public', PublicType::class)
            ->add('organizations', OrganizationType::class)
            ->add('creator', CreatorType::class)
            ->add('roles', RoleType::class)
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
