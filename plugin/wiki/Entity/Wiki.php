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
     * @ORM\Column(type="smallint", nullable=true)
     * mode of wiki
     * null or 0 : wiki open for edit
     * 1 : wiki is on moderate mode
     * 2 : wiki is on read only mode
     */
    protected $mode;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * Display or hide the section numbers in the wiki body
     */
    protected $displaySectionNumbers = false;

    //Temporary variable used only by onCopy method of WikiListener
    private $wikiCreator;

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

    /**
     * @return int
     */
    public function getMode()
    {
        return ($this->mode !== null) ? $this->mode : 0;
    }

    /**
     * @param int $mode
     */
    public function setMode($mode)
    {
        return $this->mode = $mode;
    }

    /**
     * @return bool
     */
    public function getDisplaySectionNumbers()
    {
        return $this->displaySectionNumbers;
    }

    /**
     * @param bool $displaySectionNumbers
     */
    public function setDisplaySectionNumbers($displaySectionNumbers)
    {
        return $this->displaySectionNumbers = $displaySectionNumbers;
    }

    public function getPathArray()
    {
        $path = $this->getResourceNode()->getPath();
        $pathItems = explode('`', $path);
        $pathArray = [];
        foreach ($pathItems as $item) {
            preg_match('/-([0-9]+)$/', $item, $matches);
            if (count($matches) > 0) {
                $id = substr($matches[0], 1);
                $name = preg_replace('/-([0-9]+)$/', '', $item);
                $pathArray[] = ['id' => $id, 'name' => $name];
            }
        }

        return $pathArray;
    }

    public function setWikiCreator($creator)
    {
        return $this->wikiCreator = $creator;
    }

    public function getWikiCreator()
    {
        return $this->wikiCreator;
    }

    /**
     * @ORM\PostPersist
     */
    public function createRoot(LifecycleEventArgs $event)
    {
        if ($this->getRoot() === null) {
            $em = $event->getEntityManager();
            $rootSection = $this->getRoot();
            if ($rootSection === null) {
                $rootSection = new Section();
                $rootSection->setWiki($this);
                if ($this->getResourceNode() !== null) {
                    $rootSection->setAuthor($this->getResourceNode()->getCreator());
                } else {
                    $rootSection->setAuthor($this->getWikiCreator());
                }
                $this->setRoot($rootSection);

                $em->getRepository('IcapWikiBundle:Section')->persistAsFirstChild($rootSection);
                $em->flush();
            }
        }
    }
}
