<?php
namespace Claroline\SecurityBundle\Service;

use Symfony\Component\DependencyInjection\ContainerAware;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Symfony\Component\Security\Acl\Dbal\AclProvider;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

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
