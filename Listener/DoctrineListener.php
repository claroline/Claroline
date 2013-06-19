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

        if ($entity instanceof Blog) {
            $blogOptions = new BlogOptions();
            $blogOptions->setBlog($entity);

            $entityManager->persist($blogOptions);
            $entityManager->flush($blogOptions);
        }
    }
}
