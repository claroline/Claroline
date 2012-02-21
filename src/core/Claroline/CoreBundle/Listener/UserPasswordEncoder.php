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
    
    public function prePersist(LifecycleEventArgs $event)
    {
        $user = $event->getEntity();

        if ($user instanceof User)
        {
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            $password = $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
            $user->setPassword($password);
        }
    }
    
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $user = $event->getEntity();

        if ($user instanceof User)
        {
            $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
            $password = $encoder->encodePassword($user->getPlainPassword(), $user->getSalt());
            $user->setPassword($password);
        }
    }
}