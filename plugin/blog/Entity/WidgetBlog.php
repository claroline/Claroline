<?php

namespace Icap\BlogBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *      name="icap__blog_widget_blog",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unique_widget_blog", columns={"resourceNode_id", "widgetInstance_id"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Icap\BlogBundle\Repository\WidgetBlogRepository")
 */
class WidgetBlog
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Claroline\CoreBundle\Entity\Resource\ResourceNode")
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
     * @return WidgetBlog
     */
    public function setResourceNode($resourceNode)
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
