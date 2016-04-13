<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Content;

/**
 * SubContent.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_subcontent")
 */
class SubContent
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Content")
      * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $father;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Content")
      * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $child;

    /**
     * @var string
     *
     * @ORM\Column(length=255, nullable=true)
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\SubContent")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $next;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\SubContent")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $back;

    /**
     * Constructor.
     *
     * @param \Claroline\CoreBundle\Entity\Home\SubContent $first
     */
    public function __construct(SubContent $first = null)
    {
        // the size may vary between 1 and 12 and corresponds to
        // bootstrap container col classes
        $this->size = 'content-12';

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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set size.
     *
     * @param string $size
     *
     * @return SubContent
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set father.
     *
     * @param \Claroline\CoreBundle\Entity\Content $father
     *
     * @return SubContent
     */
    public function setFather(Content $father)
    {
        $this->father = $father;

        return $this;
    }

    /**
     * Get father.
     *
     * @return \Claroline\CoreBundle\Entity\Content
     */
    public function getFather()
    {
        return $this->father;
    }

    /**
     * Set child.
     *
     * @param \Claroline\CoreBundle\Entity\Content $child
     *
     * @return SubContent
     */
    public function setChild(Content $child)
    {
        $this->child = $child;

        return $this;
    }

    /**
     * Get child.
     *
     * @return \Claroline\CoreBundle\Entity\Content
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Get child alias.
     *
     * @return \Claroline\CoreBundle\Entity\Content
     */
    public function getContent()
    {
        return $this->child;
    }

    /**
     * Set next.
     *
     * @param \Claroline\CoreBundle\Entity\Home\SubContent $next
     *
     * @return SubContent
     */
    public function setNext(SubContent $next = null)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next.
     *
     * @return \Claroline\CoreBundle\Entity\Home\SubContent
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set back.
     *
     * @param \Claroline\CoreBundle\Entity\Home\SubContent $back
     *
     * @return SubContent
     */
    public function setBack(SubContent $back = null)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get back.
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
