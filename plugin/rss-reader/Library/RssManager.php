<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\RssReaderBundle\Library;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;
use JMS\DiExtraBundle\Annotation as DI;

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
            ->findOneBy(['widgetInstance' => $config]);
    }
}
