<?php

namespace Icap\BlogBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *      name="icap__blog_widget_tag_list_blog",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(name="unique_widget_tag_list_blog", columns={"resourceNode_id", "widgetInstance_id"})
 *      }
 * )
 * @ORM\Entity(repositoryClass="Icap\BlogBundle\Repository\WidgetBlogRepository")
 */
class WidgetTagListBlog
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
     * @var int
     *
     * Option for tag cloud rendering (Classic:0, 3D sphere:1, classic with numbre of article per tag: 2:1)
     *
     * @ORM\Column(type="smallint", name="tag_cloud", nullable=false)
     */
    protected $tagCloud = 0;

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
     * @return WidgetTagListBlog
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
     * @return WidgetTagListBlog
     */
    public function setWidgetInstance(WidgetInstance $widgetInstance)
    {
        $this->widgetInstance = $widgetInstance;

        return $this;
    }

    /**
     * @param int $tagCloud
     *
     * @return WidgetTagListBlog
     */
    public function setTagCloud($tagCloud)
    {
        $this->tagCloud = $tagCloud;

        return $this;
    }

    /**
     * @return int
     */
    public function getTagCloud()
    {
        return $this->tagCloud;
    }
}
