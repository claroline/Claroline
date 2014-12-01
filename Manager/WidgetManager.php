<?php

namespace Icap\BlogBundle\Manager;

use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use Doctrine\ORM\EntityManager;
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
     * @return \Icap\BlogBundle\Entity\WidgetList[]
     */
    public function getWidgetList(WidgetInstance $widgetInstance)
    {
        return $this->entityManager
            ->getRepository('IcapBlogBundle:WidgetList')
            ->findByWidgetInstance($widgetInstance);
    }
}
 