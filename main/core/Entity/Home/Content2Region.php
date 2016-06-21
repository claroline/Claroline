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
 * Content2Region.
 *
 * @ORM\Entity()
 * @ORM\Table(name="claro_content2region")
 */
class Content2Region
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
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Region")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(length=30)
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Content2Region")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $next;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Home\Content2Region")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $back;

    /**
     * Constructor.
     *
     * @param \Claroline\CoreBundle\Entity\Home\Content2Region $first
     */
    public function __construct(Content2Region $first = null)
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
     * @return Content2Region
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
     * Set content.
     *
     * @param \Claroline\CoreBundle\Entity\Content $content
     *
     * @return Content2Region
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
     * Set region.
     *
     * @param \Claroline\CoreBundle\Entity\Home\Region $region
     *
     * @return Content2Region
     */
    public function setRegion(Region $region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region.
     *
     * @return \Claroline\CoreBundle\Entity\Home\Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set next.
     *
     * @param \Claroline\CoreBundle\Entity\Home\Content2Region $next
     *
     * @return Content2Region
     */
    public function setNext(Content2Region $next = null)
    {
        $this->next = $next;

        return $this;
    }

    /**
     * Get next.
     *
     * @return \Claroline\CoreBundle\Entity\Home\Content2Region
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * Set back.
     *
     * @param \Claroline\CoreBundle\Entity\Home\Content2Region $back
     *
     * @return Content2Region
     */
    public function setBack(Content2Region $back = null)
    {
        $this->back = $back;

        return $this;
    }

    /**
     * Get back.
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
