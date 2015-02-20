<?php

namespace Icap\BlogBundle\Manager;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\EntityManager;
use Icap\BlogBundle\Entity\WidgetListBlog;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("icap_blog.manager.widget")
 */
class WidgetManager
{
    /** @var EntityManager  */
    private $entityManager;

   /**
    * @DI\InjectParams({
    *    "entityManager" = @DI\Inject("doctrine.orm.entity_manager")
    * })
    */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return \Icap\BlogBundle\Entity\WidgetListBlog[]
     */
    public function getWidgetListBlogs(WidgetInstance $widgetInstance)
    {
        return $this->entityManager
            ->getRepository('IcapBlogBundle:WidgetListBlog')
            ->findByWidgetInstance($widgetInstance);
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return \Icap\BlogBundle\Entity\Blog[]
     */
    public function getBlogs(WidgetInstance $widgetInstance)
    {
        $resourceNodeIds = $this->getResourceNodeIds($widgetInstance);

        return $this->entityManager
            ->getRepository('IcapBlogBundle:Blog')
            ->findByResourceNodeIds($resourceNodeIds);
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return int[]
     */
    public function getResourceNodeIds(WidgetInstance $widgetInstance)
    {
        /** @var \Icap\BlogBundle\Entity\WidgetListBlog[] $widgetListBlogs */
        $widgetListBlogs =  $this->getWidgetListBlogs($widgetInstance);

        $resourceNodeIds = array();

        foreach ($widgetListBlogs as $widgetListBlog) {
            $resourceNodeIds[] = $widgetListBlog->getResourceNode()->getId();
        }

        return $resourceNodeIds;
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return \Icap\BlogBundle\Entity\Blog
     */
    public function getBlog(WidgetInstance $widgetInstance)
    {
        $resourceNode = $this->getResourceNode($widgetInstance);

        return $this->entityManager
            ->getRepository('IcapBlogBundle:Blog')
            ->findOneByResourceNode($resourceNode);
    }

    /**
     * @param WidgetInstance $widgetInstance
     *
     * @return \Claroline\CoreBundle\Entity\Resource\ResourceNode|null
     */
    public function getResourceNode(WidgetInstance $widgetInstance)
    {
        /** @var \Icap\BlogBundle\Entity\WidgetBlog $widgetBlog */
        $widgetBlog = $this->entityManager
            ->getRepository('IcapBlogBundle:WidgetBlog')
            ->findOneByWidgetInstance($widgetInstance);

        $resourceNode = null;

        if (null !== $widgetBlog) {
            $resourceNode = $widgetBlog->getResourceNode();
        }

        return $resourceNode;
    }
}
 