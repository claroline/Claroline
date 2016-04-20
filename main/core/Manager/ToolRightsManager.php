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

use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.tool_rights_manager")
 */
class ToolRightsManager
{
    private $maskManager;
    private $om;
    private $toolRightsRepo;

    /**
     * Constructor.
     *
     * @DI\InjectParams({
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "maskManager" = @DI\Inject("claroline.manager.tool_mask_decoder_manager")
     * })
     */
    public function __construct(
        ObjectManager $om,
        ToolMaskDecoderManager $maskManager
    ) {
        $this->maskManager = $maskManager;
        $this->om = $om;
        $this->toolRightsRepo = $om->getRepository('ClarolineCoreBundle:Tool\ToolRights');
    }

    public function createToolRights(OrderedTool $orderedTool, Role $role, $mask)
    {
        $toolRights = new ToolRights();
        $toolRights->setOrderedTool($orderedTool);
        $toolRights->setRole($role);
        $toolRights->setMask($mask);
        $this->om->persist($toolRights);
        $this->om->flush();
    }

    public function inverseActionValue(
        OrderedTool $orderedTool,
        Role $role,
        $action
    ) {
        $tool = $orderedTool->getTool();
        $maskDecoder = $this->maskManager
            ->getMaskDecoderByToolAndName($tool, $action);
        $maskValue = $maskDecoder->getValue();

        if (!is_null($maskDecoder)) {
            $rights = $this->toolRightsRepo
                ->findRightsByRoleAndOrderedTool($role, $orderedTool);

            if (is_null($rights)) {
                $this->createToolRights($orderedTool, $role, $maskValue);
            } else {
                $rightsMask = $rights->getMask() ^ $maskValue;
                $rights->setMask($rightsMask);
                $this->om->persist($rights);
                $this->om->flush();
            }
        }
    }

    /***** ToolRightsRepository access methods *****/

    public function getRightsByOrderedTool(
        OrderedTool $orderedTool,
        $executeQuery = true
    ) {
        return $this->toolRightsRepo->findRightsByOrderedTool(
            $orderedTool,
            $executeQuery
        );
    }

    public function getRightsByRoleAndOrderedTool(
        Role $role,
        OrderedTool $orderedTool,
        $executeQuery = true
    ) {
        return $this->toolRightsRepo->findRightsByRoleAndOrderedTool(
            $role,
            $orderedTool,
            $executeQuery
        );
    }

    public function getRightsForOrderedTools(
        array $orderedTools,
        $executeQuery = true
    ) {
        return $this->toolRightsRepo->findRightsForOrderedTools(
            $orderedTools,
            $executeQuery
        );
    }

    public function getRightsByRoleIdAndOrderedToolId($roleId, $orderedToolId)
    {
        return $this->toolRightsRepo->findOneBy(
            array('role' => $roleId, 'orderedTool' => $orderedToolId)
        );
    }
}
