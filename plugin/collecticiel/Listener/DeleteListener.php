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
use Innova\CollecticielBundle\Entity\Dropzone;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class DeleteListener
{
    use ContainerAwareTrait;

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Document) {
            if (!is_null($entity->getResourceNode())) {
                $this->container->get('claroline.manager.resource_manager')->delete($entity->getResourceNode());
            }
        } elseif ($entity instanceof Drop) {
            if (!is_null($entity->getHiddenDirectory())) {
                $this->container->get('claroline.manager.resource_manager')->delete($entity->getHiddenDirectory());
            }
        } elseif ($entity instanceof Dropzone) {
            if (!is_null($entity->getHiddenDirectory())) {
                $this->container->get('claroline.manager.resource_manager')->delete($entity->getHiddenDirectory());
            }
        }
    }
}
