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

use Claroline\AppBundle\Log\LoggableTrait;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Role;
use Claroline\CoreBundle\Entity\Tool\AdminTool;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Repository\Tool\AdministrationToolRepository;
use Claroline\CoreBundle\Repository\Tool\OrderedToolRepository;
use Claroline\CoreBundle\Repository\Tool\ToolRepository;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ToolManager implements LoggerAwareInterface
{
    use LoggableTrait;

    // todo adds a config in tools to avoid this
    const WORKSPACE_MODEL_TOOLS = ['home', 'resources', 'community', 'badges'];

    /** @var AuthorizationCheckerInterface */
    private $authorization;
    /** @var ObjectManager */
    private $om;
    /** @var ToolMaskDecoderManager */
    private $toolMaskManager;
    /** @var ToolRightsManager */
    private $toolRightsManager;

    /** @var OrderedToolRepository */
    private $orderedToolRepo;
    /** @var ToolRepository */
    private $toolRepo;
    /** @var AdministrationToolRepository */
    private $adminToolRepo;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        ObjectManager $om,
        ToolMaskDecoderManager $toolMaskManager,
        ToolRightsManager $toolRightsManager
    ) {
        $this->authorization = $authorization;
        $this->om = $om;
        $this->toolMaskManager = $toolMaskManager;
        $this->toolRightsManager = $toolRightsManager;

        $this->orderedToolRepo = $om->getRepository(OrderedTool::class);
        $this->toolRepo = $om->getRepository(Tool::class);
        $this->adminToolRepo = $om->getRepository(AdminTool::class);
    }

    public function create(Tool $tool)
    {
        $this->om->startFlushSuite();
        $this->om->persist($tool);
        $this->om->forceFlush();
        $this->toolMaskManager->createDefaultToolMaskDecoders($tool);
        $this->om->endFlushSuite();

        if ($tool->isDisplayableInWorkspace()) {
            // check if there are already workspace tools, if not we add them
            $ot = $this->om->getRepository(OrderedTool::class)->findBy(['tool' => $tool], [], 1, 0);
            if (0 === count($ot)) {
                $offset = 0;
                $totalTools = $this->om->count(Tool::class);
                $total = $this->om->count(Workspace::class);
                $this->log('Adding tool '.$tool->getName().' to workspaces ('.$total.')');

                $this->om->startFlushSuite();

                while ($offset < $total) {
                    /** @var Workspace $workspaces */
                    $workspaces = $this->om->getRepository(Workspace::class)->findBy([], [], 500, $offset);

                    foreach ($workspaces as $workspace) {
                        $this->setWorkspaceTool($tool, $totalTools, $workspace);
                        ++$offset;
                        $this->log('Adding tool '.$offset.'/'.$total);
                    }

                    $this->log('Flush');
                    $this->om->forceFlush();
                }

                $this->om->endFlushSuite();
            }
        }

        if ($tool->isDisplayableInDesktop()) {
            // check if there is already desktop tool, if not we add it
            $ot = $this->om->getRepository(OrderedTool::class)->findBy(['tool' => $tool, 'workspace' => null, 'user' => null], [], 1, 0);
            if (0 === count($ot)) {
                $desktopTools = $this->om->getRepository(OrderedTool::class)->findBy(['workspace' => null, 'user' => null]);

                $orderedTool = new OrderedTool();
                $orderedTool->setWorkspace(null);
                $orderedTool->setUser(null);
                $orderedTool->setTool($tool);
                $orderedTool->setOrder(count($desktopTools) + 1);

                $this->om->persist($orderedTool);
                $this->om->flush();
            }
        }
    }

    public function getCurrentPermissions(OrderedTool $orderedTool)
    {
        $decoders = $this->toolMaskManager->getMaskDecodersByTool($orderedTool->getTool());

        // certainly not the optimal way to generate it, but it avoids to replicate logic from OrderedToolVoter
        $perms = [];
        foreach ($decoders as $decoder) {
            $perms[$decoder->getName()] = $this->authorization->isGranted($decoder->getName(), $orderedTool);
        }

        return $perms;
    }

    /**
     * @deprecated can be done by the ToolRightsSerializer
     */
    public function setPermissions(array $perms, OrderedTool $orderedTool, Role $role)
    {
        $mask = $this->toolMaskManager->encodeMask($perms, $orderedTool->getTool());
        $this->toolRightsManager->setToolRights($orderedTool, $role, $mask);
    }

    public function getOrderedTool(string $name, string $context, string $contextId = null): ?OrderedTool
    {
        /** @var OrderedTool|null $orderedTool */
        $orderedTool = null;
        switch ($context) {
            case Tool::DESKTOP:
                $orderedTool = $this->orderedToolRepo->findOneByNameAndDesktop($name);

                break;
            case Tool::WORKSPACE:
                $contextObject = $this->om->getRepository(Workspace::class)->findOneBy(['uuid' => $contextId]);
                $orderedTool = $this->orderedToolRepo->findOneByNameAndWorkspace($name, $contextObject);

                break;
            case Tool::ADMINISTRATION:
                // implement later
                break;
        }

        return $orderedTool;
    }

    /**
     * @return OrderedTool[]
     */
    public function getOrderedToolsByDesktop(array $roles = []): array
    {
        if (empty($roles) || in_array('ROLE_ADMIN', $roles)) {
            return $this->orderedToolRepo->findByDesktop();
        }

        return $this->orderedToolRepo->findByDesktopAndRoles($roles);
    }

    /**
     * @return OrderedTool[]
     */
    public function getOrderedToolsByWorkspace(Workspace $workspace, array $roles = []): array
    {
        if (empty($roles)) {
            $tools = $this->orderedToolRepo->findByWorkspace($workspace);
        } else {
            $tools = $this->orderedToolRepo->findByWorkspaceAndRoles($workspace, $roles);
        }

        /*if ($workspace->isModel()) {
            $tools = array_filter($tools, function (OrderedTool $orderedTool) {
                return in_array($orderedTool->getTool()->getName(), static::WORKSPACE_MODEL_TOOLS);
            });
        }*/

        return $tools;
    }

    /**
     * @param string $name
     *
     * @return AdminTool
     */
    public function getAdminToolByName($name)
    {
        /** @var AdminTool $adminTool */
        $adminTool = $this->adminToolRepo->findOneBy(['name' => $name]);

        return $adminTool;
    }

    /**
     * @return AdminTool[]
     */
    public function getAdminToolsByRoles(array $roles)
    {
        return $this->adminToolRepo->findByRoles($roles);
    }

    public function getToolByName($name)
    {
        return $this->toolRepo->findOneBy(['name' => $name]);
    }

    /**
     * Adds the tools missing in the database for a workspace.
     */
    public function addMissingWorkspaceTools(Workspace $workspace)
    {
        $undisplayedTools = $this->toolRepo->findUndisplayedToolsByWorkspace($workspace);
        if (0 === count($undisplayedTools)) {
            return;
        }

        $initPos = $this->toolRepo->countDisplayedToolsByWorkspace($workspace);
        ++$initPos;

        $this->om->startFlushSuite();

        foreach ($undisplayedTools as $undisplayedTool) {
            $wot = $this->orderedToolRepo->findOneBy([
                'workspace' => $workspace,
                'tool' => $undisplayedTool,
            ]);

            //create a WorkspaceOrderedTool for each Tool that hasn't already one
            if (null === $wot) {
                $this->setWorkspaceTool(
                    $undisplayedTool,
                    $initPos,
                    $workspace
                );

                ++$initPos;
            }
        }

        $this->om->endFlushSuite();
    }

    /**
     * @param int $position
     *
     * @return OrderedTool
     */
    private function setWorkspaceTool(Tool $tool, $position, Workspace $workspace)
    {
        $orderedTool = $this->orderedToolRepo->findOneBy([
            'workspace' => $workspace,
            'tool' => $tool,
        ]);

        if (!$orderedTool) {
            $orderedTool = new OrderedTool();
        }

        $switchTool = null;
        // At the workspace creation, the workspace id is still null because we only flush once at the very end.
        if (null !== $workspace->getId()) {
            $switchTool = $this->orderedToolRepo->findOneBy([
                'workspace' => $workspace,
                'order' => $position,
            ]);
        }

        while (!is_null($switchTool)) {
            ++$position;
            $switchTool = $this->orderedToolRepo->findOneBy([
                'workspace' => $workspace,
                'order' => $position,
            ]);
        }

        $orderedTool->setWorkspace($workspace);
        $orderedTool->setOrder($position);
        $orderedTool->setTool($tool);

        $this->om->persist($orderedTool);
        $this->om->flush();

        return $orderedTool;
    }
}
