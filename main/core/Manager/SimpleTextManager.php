<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager;

use JMS\DiExtraBundle\Annotation as DI;
use Claroline\CoreBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Widget\WidgetInstance;

/**
 * @DI\Service("claroline.manager.simple_text_manager")
 */
class SimpleTextManager
{
    private $om;

    /**
     * @DI\InjectParams({
     *       "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param \Claroline\CoreBundle\Entity\Widget\WidgetInstance $config
     *
     * @return \Claroline\CoreBundle\Entity\Widget\SimpleTextConfig
     */
    public function getTextConfig(WidgetInstance $config)
    {
        return $this->om
            ->getRepository('ClarolineCoreBundle:Widget\SimpleTextConfig')
            ->findOneBy(array('widgetInstance' => $config));
    }
}
