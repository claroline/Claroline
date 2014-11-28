<?php

namespace Icap\BlogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Icap\BlogBundle\Entity\Blog;
use Symfony\Component\Validator\Constraints as Assert;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

/**
 * @ORM\Table(name="icap__blog_widget_list")
 * @ORM\Entity
 */
class WidgetList
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Icap\BlogBundle\Entity\Blog")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var \Icap\BlogBundle\Entity\Blog
     */
    protected $blog;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @var \Claroline\CoreBundle\Entity\Widget\WidgetInstance
     */
    protected $widgetInstance;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Icap\BlogBundle\Entity\Blog
     */
    public function getBlog()
    {
        return $this->blog;
    }

    /**
     * @param \Icap\BlogBundle\Entity\Blog $blog
     *
     * @return WidgetList
     */
    public function setBlog(Blog $blog)
    {
        $this->blog = $blog;

        return $this;
    }

    /**
     * @return WidgetInstance
     */
    public function getWidgetInstance()
    {
        return $this->widgetInstance;
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return WidgetList
     */
    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;

        return $this;
    }
}
