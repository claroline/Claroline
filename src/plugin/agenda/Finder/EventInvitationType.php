<?php

namespace Claroline\AgendaBundle\Finder;

use Claroline\AgendaBundle\Entity\EventInvitation;
use Claroline\AppBundle\API\Finder\AbstractType;
use Claroline\AppBundle\API\Finder\FinderBuilderInterface;
use Claroline\AppBundle\API\Finder\Type\ChoiceType;
use Claroline\AppBundle\API\Finder\Type\EntityType;
use Claroline\CommunityBundle\Finder\UserType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventInvitationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventInvitation::class,
        ]);
    }

    public function buildFinder(FinderBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', UserType::class)
            ->add('event', EventType::class)
            ->add('status', ChoiceType::class, [
                'choices' => [EventInvitation::UNKNOWN, EventInvitation::JOIN, EventInvitation::MAYBE, EventInvitation::RESIGN],
            ])
        ;
    }

    public function getParent(): ?string
    {
        return EntityType::class;
    }
}
