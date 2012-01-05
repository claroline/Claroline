<?php

namespace Claroline\SecurityBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Security\Acl\Dbal\AclProvider;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;

class AclPersister extends ContainerAware implements EventSubscriber
{
    /** @return AclProvider */
    private function getAclProvider()
    {
        return $this->container->get('security.acl.provider');
    }
    
    public function getSubscribedEvents()
    {
        return array(Events::postPersist, Events::preRemove); 
    }
    
    public function postPersist(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $oid = ObjectIdentity::fromDomainObject($entity);
        $this->getAclProvider()->createAcl($oid);
    }
    
    public function preRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $oid = ObjectIdentity::fromDomainObject($entity);
        $this->getAclProvider()->deleteAcl($oid);
    }
}