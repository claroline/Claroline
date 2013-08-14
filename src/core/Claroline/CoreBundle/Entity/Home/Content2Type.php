<?php

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content2Type
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_content2type")
 */
class Content2Type
{
    /**
     * @var integer
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Content")
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
        // the size may vary between 1 and 12 and corresponds to
        // bootstrap container col classes
        $this->size = 'col-lg-12';

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
     * @param  string       $size
     * @return Content2Type
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
     * Set content
     *
     * @param  \Claroline\CoreBundle\Entity\Home\Content $content
     * @return Content2Type
     */
    public function setContent(Content $content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set type
     *
     * @param  \Claroline\CoreBundle\Entity\Home\Type $type
     * @return Content2Type
     */
    public function setType(Type $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Claroline\CoreBundle\Entity\Home\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set next
     *
     * @param  \Claroline\CoreBundle\Entity\Home\Content2Type $next
     * @return Content2Type
     */
    public function setNext(Content2Type $next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content2Type
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set back
     *
     * @param  \Claroline\CoreBundle\Entity\Home\Content2Type $back
     * @return Content2Type
     */
    public function setBack(Content2Type $back)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get back
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
}
