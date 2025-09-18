<?php

namespace Claroline\AuthenticationBundle\Finder;

use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\AppBundle\API\Finder\Type\TextType;
use Claroline\AuthenticationBundle\Entity\IpUser;
use Claroline\CommunityBundle\Finder\UserType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IpUserType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IpUser::class,
            'fulltext' => ['ip'],
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ip', TextType::class)
            ->add('user', UserType::class, ['nullable' => true])
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
