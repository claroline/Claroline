<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Resource;

use Claroline\CoreBundle\Entity\Resource\ResourceNode;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.resource_menu_manager")
 */
class ResourceMenuManager
{
    private $om;

    /**
     * @DI\InjectParams({
     *      "om" = @DI\Inject("claroline.persistence.object_manager")
     * })
     */
    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;
    }

    public function getMenus(ResourceNode $resourceNode)
    {
        $specificMenus = $resourceNode->getResourceType()->getActions();

        return array_merge(
            $specificMenus->toArray(),
            $this->om->getRepository('ClarolineCoreBundle:Resource\MenuAction')->findBy(['resourceType' => null])
        );
    }
}
