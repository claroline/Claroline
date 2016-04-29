<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vincent
 * Date: 23/09/13
 * Time: 17:06
 * To change this template use File | Settings | File Templates.
 */

namespace Innova\CollecticielBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Innova\CollecticielBundle\Entity\Document;
use Proxies\__CG__\Innova\CollecticielBundle\Entity\Dropzone;
use Symfony\Component\DependencyInjection\ContainerAware;

class DeleteListener extends ContainerAware
{
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Document) {
            if ($entity->getResourceNode() != null) {
                $this->container->get('claroline.manager.resource_manager')->delete($entity->getResourceNode());
            }
        } elseif ($entity instanceof Drop) {
            if ($entity->getHiddenDirectory() != null) {
                $this->container->get('claroline.manager.resource_manager')->delete($entity->getHiddenDirectory());
            }
        } elseif ($entity instanceof Dropzone) {
            if ($entity->getHiddenDirectory() != null) {
                $this->container->get('claroline.manager.resource_manager')->delete($entity->getHiddenDirectory());
            }
        }
    }
}
