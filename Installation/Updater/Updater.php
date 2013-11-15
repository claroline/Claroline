<?php
namespace Icap\BlogBundle\Installation\Updater;

use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\EntityManager;

class Updater
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postUpdate()
    {
        $this->UpdateMissingSlug();
    }

    public function UpdateMissingSlug(){
        $tags = $this->entityManager->getRepository("IcapBlogBundle:Tag")->findAllBy(array('slug' => null));

        foreach ($tags as $tag) {
            $tag->setSlug(uniqid());
        }
        $this->entityManager->flush();

        foreach ($tags as $tag) {
            $tag->setSlug(null);
        }
        $this->entityManager->flush();
    }

}