<?php

namespace Claroline\CoreBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Claroline\CoreBundle\Entity\User;

class UserPasswordEncoder extends ContainerAware implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return array(Events::prePersist, Events::preUpdate);
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $user = $eventArgs->getEntity();

        if ($user instanceof User) {
            $password = $this->encodePassword($user);
            $user->setPassword($password);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $user = $eventArgs->getEntity();

        if ($user instanceof User) {
            if ($eventArgs->hasChangedField('password')) {
                $password = $this->encodePassword($user);
                $eventArgs->setNewValue('password', $password);
            }
        }
    }

    private function encodePassword(User $user)
    {
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);

        return $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
    }
}