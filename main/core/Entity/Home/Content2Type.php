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
 * Content2Type.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_content2type")
 */
class Content2Type
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
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Type")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(length=30)
     */
    private $size;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $collapse;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Content2Type")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $next;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Content2Type")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $back;

    /**
     * Constructor.
     *
     * @param \Claroline\CoreBundle\Entity\Home\Content2Type $first
     */
    public function __construct(Content2Type $first = null)
    {
        $this->setFirst($first);
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
     * @return Content2Type
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
     * Set collapse.
     *
     * @param bool $collapse
     *
     * @return Content2Type
     */
    public function setCollapse($collapse)
    {
        $this->collapse = $collapse;

        return $this;
    }

    /**
     * Get collapse.
     *
     * @return bool
     */
    public function isCollapse()
    {
        return $this->collapse;
    }

    /**
     * Set content.
     *
     * @param \Claroline\CoreBundle\Entity\Content $content
     *
     * @return Content2Type
     */
    public function setContent(Content $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content.
     *
     * @return \Claroline\CoreBundle\Entity\Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set type.
     *
     * @param \Claroline\CoreBundle\Entity\Home\Type $type
     *
     * @return Content2Type
     */
    public function setType(Type $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return \Claroline\CoreBundle\Entity\Home\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set next.
     *
     * @param \Claroline\CoreBundle\Entity\Home\Content2Type $next
     *
     * @return Content2Type
     */
    public function setNext(Content2Type $next = null)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next.
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content2Type
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set back.
     *
     * @param \Claroline\CoreBundle\Entity\Home\Content2Type $back
     *
     * @return Content2Type
     */
    public function setBack(Content2Type $back = null)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get back.
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content2Type
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

    /**
     *
     */
    public function setFirst(Content2Type $first = null)
    {
        // the size may vary between 1 and 12 and corresponds to
        // bootstrap container col classes
        $this->setSize('content-12');
        $this->setCollapse(false);

        if ($first) {
            $first->setBack($this);
            $this->next = $first;
            $this->back = null;
        } else {
            $this->next = null;
            $this->back = null;
        }
    }
}
