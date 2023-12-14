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
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ToolManager
{
    private OrderedToolRepository $orderedToolRepo;

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorization,
        private readonly ObjectManager $om,
        private readonly ToolMaskDecoderManager $toolMaskManager,
        private readonly ToolRightsManager $toolRightsManager
    ) {
        $this->orderedToolRepo = $om->getRepository(OrderedTool::class);
    }

    public function getCurrentPermissions(OrderedTool $orderedTool): array
    {
        $decoders = $this->toolMaskManager->getMaskDecodersByTool($orderedTool->getName());

        // certainly not the optimal way to generate it, but it avoids to replicate logic from OrderedToolVoter
        $perms = [];
        foreach ($decoders as $decoder) {
            $perms[$decoder->getName()] = $this->authorization->isGranted($decoder->getName(), $orderedTool);
        }

        return $perms;
    }

    public function getPermissions(OrderedTool $orderedTool, Role $role): array
    {
        $toolRights = $this->om->getRepository(ToolRights::class)->findBy([
            'role' => $role,
            'orderedTool' => $orderedTool,
        ]);

        $mask = 0 < count($toolRights) ? $toolRights[0]->getMask() : 0;

        return $this->toolMaskManager->decodeMask($mask, $orderedTool->getName());
    }

    /**
     * @deprecated can be done by the ToolRightsSerializer
     */
    public function setPermissions(array $perms, OrderedTool $orderedTool, Role $role): void
    {
        $mask = $this->toolMaskManager->encodeMask($perms, $orderedTool->getName());
        $this->toolRightsManager->setToolRights($orderedTool, $role, $mask);
    }

    public function getOrderedTool(string $name, string $context, string $contextId = null): ?OrderedTool
    {
        return $this->orderedToolRepo->findOneByNameAndContext($name, $context, $contextId);
    }

    /**
     * @return OrderedTool[]
     */
    public function getOrderedTools(string $context, string $contextId = null, ?array $roles = []): array
    {
        return $this->orderedToolRepo->findByContext($context, $contextId, $roles);
    }
}
