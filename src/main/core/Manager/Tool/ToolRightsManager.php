<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Manager\Tool;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Repository\Tool\ToolRightsRepository;

class ToolRightsManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ToolRightsRepository */
    private $toolRightsRepo;

    /**
     * ToolRightsManager constructor.
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;

        $this->toolRightsRepo = $om->getRepository(ToolRights::class);
    }

    /**
     * @deprecated can be done by the ToolRightsSerializer
     */
    public function setToolRights(OrderedTool $orderedTool, Role $role, $mask)
    {
        $toolRights = $this->toolRightsRepo->findOneBy(['role' => $role, 'orderedTool' => $orderedTool]);
        if (!$toolRights) {
            $toolRights = new ToolRights();
        }

        $toolRights->setOrderedTool($orderedTool);
        $toolRights->setRole($role);
        $toolRights->setMask($mask);

        $this->om->persist($toolRights);
        $this->om->flush();
    }
}
