<?php

namespace Claroline\CoreBundle\Entity\Node;

use Doctrine\ORM\Mapping as ORM;

/**
 * Link
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_nodelink")
 */
class Link
{
    public function __construct($first)
    {
        $this->size = "12";

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
     * @var \Claroline\CoreBundle\Entity\Node\Node
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Node\Node")
     * @ORM\JoinColumn(name="a_id", nullable=false, onDelete="CASCADE")
    */
    private $a;

    /**
     * @var \Claroline\CoreBundle\Entity\Node\Node
     *
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Node\Node")
     * @ORM\JoinColumn(name="b_id", nullable=false, onDelete="CASCADE")
    */
    private $b;

    /**
     * @var integer
     *
     * @ORM\Column(name="size", type="integer")
     */
    private $size;

    /**
    * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Node\Link")
    * @ORM\JoinColumn(name="next_id", nullable=true, onDelete="CASCADE")
    */
    private $next;

    /**
    * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Node\Link")
    * @ORM\JoinColumn(name="back_id", nullable=true, onDelete="CASCADE")
    */
    private $back;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Node")
     * @ORM\JoinColumn(name="type", nullable=false)
     */
    private $type;

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
     * @param integer $size
     * @return Link
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set A
     *
     * @param \Claroline\CoreBundle\Entity\Node\Node $a
     * @return Link
     */
    public function setA(\Claroline\CoreBundle\Entity\Node\Node $a)
    {
        $this->a = $a;

        return $this;
    }

    /**
     * Get A
     *
     * @return \Claroline\CoreBundle\Entity\Node\Node
     */
    public function getA()
    {
        return $this->a;
    }

    /**
     * Set B
     *
     * @param \Claroline\CoreBundle\Entity\Node\Node $b
     * @return Link
     */
    public function setB(\Claroline\CoreBundle\Entity\Node\Node $b)
    {
        $this->b = $b;

        return $this;
    }

    /**
     * Get B
     *
     * @return \Claroline\CoreBundle\Entity\Node\Node
     */
    public function getB()
    {
        return $this->b;
    }

    /**
     * Set next
     *
     * @param \Claroline\CoreBundle\Entity\Node\Link $next
     * @return Link
     */
    public function setNext($next)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next
     *
     * @return \Claroline\CoreBundle\Entity\Node\Link
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set back
     *
     * @param \Claroline\CoreBundle\Entity\Node\Link $back
     * @return Link
     */
    public function setBack($back)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get back
     *
     * @return \Claroline\CoreBundle\Entity\Node\Link
     */
    public function getBack()
    {
        return $this->back;
    }

    /**
     * Set type
     *
     * @param \Claroline\CoreBundle\Entity\Node $type
     * @return Link
     */
    public function setType(\Claroline\CoreBundle\Entity\Node $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \Claroline\CoreBundle\Entity\Node
     */
    public function getType()
    {
        return $this->type;
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
