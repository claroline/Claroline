<?php

namespace Claroline\CoreBundle\Entity\Home;

use Doctrine\ORM\Mapping as ORM;

/**
 * Content2Region
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_content2region")
 */
class Content2Region
{
    public function __construct($first)
    {
        $this->size = "span12"; //The size may be between 1 and 12 that correspont to span1 ant span12 of bootstrap

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
     * @ORM\JoinColumn(name="content_id", nullable=false, onDelete="CASCADE")
    */
    private $content;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Region")
     * @ORM\JoinColumn(name="region_id", nullable=false, onDelete="CASCADE")
    */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="size", type="string", length=30)
     */
    private $size;

    /**
    * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Content2Region")
    * @ORM\JoinColumn(name="next_id", nullable=true, onDelete="CASCADE")
    */
    private $next;

    /**
    * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Content2Region")
    * @ORM\JoinColumn(name="back_id", nullable=true, onDelete="CASCADE")
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
     * @param  string         $size
     * @return Content2Region
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
     * @return Content2Region
     */
    public function setContent(\Claroline\CoreBundle\Entity\Home\Content $content)
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
     * Set region
     *
     * @param  \Claroline\CoreBundle\Entity\Home\Region $region
     * @return Content2Region
     */
    public function setRegion(\Claroline\CoreBundle\Entity\Home\Region $region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \Claroline\CoreBundle\Entity\Home\Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set next
     *
     * @param  \Claroline\CoreBundle\Entity\Home\Content2Region $next
     * @return Content2Region
     */
    public function setNext(\Claroline\CoreBundle\Entity\Home\Content2Region $next = null)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content2Region
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set back
     *
     * @param  \Claroline\CoreBundle\Entity\Home\Content2Region $back
     * @return Content2Region
     */
    public function setBack(\Claroline\CoreBundle\Entity\Home\Content2Region $back = null)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get back
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content2Region
     */
    public function getBack()
    {
        return $this->back;
    }

    /**
     * Detach a content from a type, this function can be used for reorder or delete contents in regions.
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
