<?php

namespace Icap\WikiBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity
* @ORM\Table(name="icap__wiki")
* @ORM\HasLifecycleCallbacks()
*/
class Wiki extends AbstractResource
{
    /**
     * @ORM\OneToOne(targetEntity="Icap\WikiBundle\Entity\Section", cascade={"all"})
     * @ORM\JoinColumn(name="root_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $root;

    /**
     * @param mixed $root
     */
    public function setRoot($root)
    {
        $this->root = $root;
    }

    /**
     * @return mixed
     */
    public function getRoot()
    {
        return $this->root;
    }

    public function getPathArray()
    {
        $path = $this->getResourceNode()->getPath();
        $pathItems = explode("`", $path);
        $pathArray = array();
        foreach ($pathItems as $item) {
            preg_match("/-([0-9]+)$/", $item, $matches);
            if (count($matches) > 0) {
                $id = substr($matches[0], 1);
                $name = preg_replace("/-([0-9]+)$/", "", $item);
                $pathArray[] = array('id' => $id, 'name' => $name);
            }
        }

        return $pathArray;
    }

    /**
     * @ORM\PostPersist
     */
    public function createRoot(LifecycleEventArgs $event){
        if($this->getRoot() == null){
            $em = $event->getEntityManager();
            $rootSection = $this->getRoot();
            if($rootSection == null){

                $rootSection = new Section();
                $rootSection->setTitle("");
                $rootSection->setWiki($this);
                $this->setRoot($rootSection);

                $em->getRepository('IcapWikiBundle:Section')->persistAsFirstChild($rootSection);
                $em->flush();
            }
        }
    }
}