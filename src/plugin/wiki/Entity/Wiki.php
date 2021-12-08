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
    const OPEN_MODE = 0;
    const MODERATE_MODE = 1;
    const READ_ONLY_MODE = 2;

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

    /**
     * @ORM\Column(type="boolean", name="display_contents", nullable=false, options={"default": true})
     * Display or hide the section numbers in the wiki body
     */
    protected $displayContents = true;

    //Temporary variable used only by onCopy method of WikiListener
    private $wikiCreator;

    /**
     * @param mixed $root
     *
     * @return $this
     */
    public function setRoot($root)
    {
        $this->root = $root;

        return $this;
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
        return (null !== $this->mode) ? $this->mode : self::OPEN_MODE;
    }

    /**
     * @param int $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
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
     *
     * @return $this
     */
    public function setDisplaySectionNumbers($displaySectionNumbers)
    {
        $this->displaySectionNumbers = $displaySectionNumbers;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDisplayContents()
    {
        return $this->displayContents;
    }

    /**
     * @param mixed $displayContents
     *
     * @return $this
     */
    public function setDisplayContents($displayContents)
    {
        $this->displayContents = $displayContents;

        return $this;
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
        $this->wikiCreator = $creator;

        return $this;
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
        $em = $event->getEntityManager();
        $rootSection = $this->buildRoot();
        $em->getRepository('IcapWikiBundle:Section')->persistAsFirstChild($rootSection);
        $em->flush();
    }

    public function buildRoot()
    {
        $rootSection = $this->getRoot();

        if (!$rootSection) {
            $rootSection = new Section();
            $rootSection->setWiki($this);
            if (null !== $this->getResourceNode()) {
                $rootSection->setAuthor($this->getResourceNode()->getCreator());
            } else {
                $rootSection->setAuthor($this->getWikiCreator());
            }
            $this->setRoot($rootSection);
        }

        return $rootSection;
    }
}
