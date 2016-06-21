<?php

namespace Icap\BlogBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Doctrine\ORM\Mapping as ORM;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

/**
 * @ORM\Table(name="icap__blog_widget_list_blog")
 * @ORM\Entity(repositoryClass="Icap\BlogBundle\Repository\WidgetListBlogRepository")
 */
class WidgetListBlog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var \Claroline\CoreBundle\Entity\Resource\ResourceNode
     */
    protected $resourceNode;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Widget\WidgetInstance")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     *
     * @var \Claroline\CoreBundle\Entity\Widget\WidgetInstance
     */
    protected $widgetInstance;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ResourceNode
     */
    public function getResourceNode()
    {
        return $this->resourceNode;
    }

    /**
     * @param ResourceNode $resourceNode
     *
     * @return WidgetListBlog
     */
    public function setResourceNode(ResourceNode $resourceNode)
    {
        $this->resourceNode = $resourceNode;

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
