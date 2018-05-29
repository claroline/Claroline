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

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Repository\ToolRightsRepository;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.manager.tool_rights_manager")
 *
 * @todo move me in Tool namespace
 */
class ToolRightsManager
{
    /** @var ObjectManager */
    private $om;

    /** @var ToolMaskDecoderManager */
    private $maskManager;

    /** @var ToolRightsRepository */
    private $toolRightsRepo;

    /**
     * ToolRightsManager constructor.
     *
     * @DI\InjectParams({
     *     "om"          = @DI\Inject("claroline.persistence.object_manager"),
     *     "maskManager" = @DI\Inject("claroline.manager.tool_mask_decoder_manager")
     * })
     *
     * @param ObjectManager          $om
     * @param ToolMaskDecoderManager $maskManager
     */
    public function __construct(
        ObjectManager $om,
        ToolMaskDecoderManager $maskManager)
    {
        $this->om = $om;
        $this->maskManager = $maskManager;

        $this->toolRightsRepo = $om->getRepository('ClarolineCoreBundle:Tool\ToolRights');
    }

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

    public function inverseActionValue(OrderedTool $orderedTool, Role $role, $action)
    {
        $rightsMask = null;
        $tool = $orderedTool->getTool();
        $maskDecoder = $this->maskManager
            ->getMaskDecoderByToolAndName($tool, $action);
        $maskValue = $maskDecoder->getValue();

        if (!is_null($maskDecoder)) {
            $rights = $this->toolRightsRepo
                ->findRightsByRoleAndOrderedTool($role, $orderedTool);

            if (is_null($rights)) {
                $this->setToolRights($orderedTool, $role, $maskValue);
            } else {
                $rightsMask = $rights->getMask() ^ $maskValue;
                $rights->setMask($rightsMask);
                $this->om->persist($rights);
                $this->om->flush();
            }
        }
    }

    public function getRightsByOrderedTool(OrderedTool $orderedTool, $executeQuery = true)
    {
        return $this->toolRightsRepo->findRightsByOrderedTool(
            $orderedTool,
            $executeQuery
        );
    }

    public function getRightsByRoleAndOrderedTool(Role $role, OrderedTool $orderedTool, $executeQuery = true)
    {
        return $this->toolRightsRepo->findRightsByRoleAndOrderedTool(
            $role,
            $orderedTool,
            $executeQuery
        );
    }

    public function getRightsForOrderedTools(array $orderedTools, $executeQuery = true)
    {
        return $this->toolRightsRepo->findRightsForOrderedTools(
            $orderedTools,
            $executeQuery
        );
    }

    public function getRightsByRoleIdAndOrderedToolId($roleId, $orderedToolId)
    {
        return $this->toolRightsRepo->findOneBy(
            ['role' => $roleId, 'orderedTool' => $orderedToolId]
        );
    }
}
