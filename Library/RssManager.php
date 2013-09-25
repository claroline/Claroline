<?php

namespace Claroline\RssReaderBundle\Library;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

/**
 * @DI\Service("claroline.manager.rss_manager")
 */
class RssManager
{
    private $om;

   /**
    * @DI\InjectParams({
    *    "om" = @DI\Inject("claroline.persistence.object_manager")
    * })
    */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    public function getConfig(WidgetInstance $config)
    {
        return $this->om
            ->getRepository('ClarolineRssReaderBundle:Config')
            ->findOneBy(array('widgetInstance' => $config));
    }
}
