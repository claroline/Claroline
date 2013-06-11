<?php

namespace ICAP\BlogBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use ICAP\BlogBundle\Entity\Blog;
use ICAP\BlogBundle\Entity\BlogOptions;

class DoctrineListener  
{
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        // peut-être voulez-vous seulement agir sur une entité « Product »
        if ($entity instanceof Blog) {
            // faites quelque chose avec l'entité « Product »
            $blogOptions = new BlogOptions();
            $blogOptions->setBlog($entity);

            $entityManager->persist($blogOptions);
            $entityManager->flush($blogOptions);
        }
    }
}
