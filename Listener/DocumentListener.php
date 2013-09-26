<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vincent
 * Date: 23/09/13
 * Time: 17:06
 * To change this template use File | Settings | File Templates.
 */

namespace Icap\DropzoneBundle\Listener;


use Doctrine\ORM\Event\LifecycleEventArgs;
use Icap\DropzoneBundle\Entity\Document;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DocumentListener extends ContainerAware
{
    public function preRemove(LifecycleEventArgs $args)
    {
        $document = $args->getEntity();
        if ($document instanceof Document) {
            if ($document->getResourceNode() != null) {
                $this->container->get('claroline.manager.resource_manager')->delete($document->getResourceNode());
            }
        }
    }
}