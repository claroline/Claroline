<?php

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;

/**
 * SubContent
 *
 * @ORM\Table(name="claro_subcontent")
 * @ORM\Entity(repositoryClass="Claroline\CoreBundle\Repository\SubContentRepository")
 */
class SubContent
{
    public function __construct($first)
    {
        $this->size = "span12"; //The size may be between 1 and 12 that correspont to span1 and span12 of bootstrap

        if ($first) {
            $first->setBack($this);
            $this->next = $first;
            $this->back = null;
        } else {
            $this->next = null;
            $this->back = null;
        }
    }

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

     /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Content")
     * @ORM\JoinColumn(nullable=false)
    */
    private $father;

     /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Content")
     * @ORM\JoinColumn(nullable=false)
    */
    private $child;

        /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=255)
     */
    private $size;

    /**
    * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\SubContent")
    */
    private $next;

    /**
    * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\SubContent")
    */
    private $back;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set size
     *
     * @param string $size
     * @return SubContent
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set father
     *
     * @param \Claroline\CoreBundle\Entity\Home\Content $father
     * @return SubContent
     */
    public function setFather(\Claroline\CoreBundle\Entity\Home\Content $father)
    {
        $this->father = $father;

        return $this;
    }

    /**
     * Get father
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content
     */
    public function getFather()
    {
        return $this->father;
    }

    /**
     * Set child
     *
     * @param \Claroline\CoreBundle\Entity\Home\Content $child
     * @return SubContent
     */
    public function setChild(\Claroline\CoreBundle\Entity\Home\Content $child)
    {
        $this->child = $child;

        return $this;
    }

    /**
     * Get child
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Get child alias
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content
     */
    public function getContent()
    {
        return $this->child;
    }

    /**
     * Set next
     *
     * @param \Claroline\CoreBundle\Entity\Home\SubContent $next
     * @return SubContent
     */
    public function setNext(\Claroline\CoreBundle\Entity\Home\SubContent $next = null)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next
     *
     * @return \Claroline\CoreBundle\Entity\Home\SubContent
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set back
     *
     * @param \Claroline\CoreBundle\Entity\Home\SubContent $back
     * @return SubContent
     */
    public function setBack(\Claroline\CoreBundle\Entity\Home\SubContent $back = null)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get back
     *
     * @return \Claroline\CoreBundle\Entity\Home\SubContent
     */
    public function getBack()
    {
        return $this->back;
    }

    /**
     * Detach a content from a type, this function can be used for reorder or delete contents.
     */
    public function detach()
    {
        if ($this->getBack()) {
            $this->getBack()->setNext($this->getNext());
        }

        if ($this->getNext()) {
            $this->getNext()->setBack($this->getBack());
        }
    }
}
