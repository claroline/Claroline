<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter\Tool;

use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;
use Claroline\CoreBundle\Manager\Workspace\WorkspaceManager;
use Claroline\CoreBundle\Repository\Tool\ToolRightsRepository;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Role\Role;

/**
 * Checks if the current token can access a tool configured in a Workspace or Desktop
 * (it should also check for admin tools later).
 */
class OrderedToolVoter extends AbstractVoter
{
    /** @var ToolMaskDecoderManager */
    private $maskManager;

    /** @var WorkspaceManager */
    private $workspaceManager;

    /** @var ToolRightsRepository */
    private $rightsRepository;

    /**
     * OrderedToolVoter constructor.
     *
     * @param ObjectManager          $om
     * @param ToolMaskDecoderManager $maskManager
     * @param WorkspaceManager       $workspaceManager
     */
    public function __construct(
        ObjectManager $om,
        ToolMaskDecoderManager $maskManager,
        WorkspaceManager $workspaceManager
    ) {
        $this->maskManager = $maskManager;
        $this->workspaceManager = $workspaceManager;

        $this->rightsRepository = $om->getRepository(ToolRights::class);
    }

    /**
     * @param TokenInterface $token
     * @param OrderedTool    $object
     * @param array          $attributes
     * @param array          $options
     *
     * @return int
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        if ($this->isAdmin($token)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if (!empty($object->getWorkspace()) && $this->workspaceManager->isManager($object->getWorkspace(), $token)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $decoder = $this->maskManager->getMaskDecoderByToolAndName($object->getTool(), $attributes[0]);
        if ($decoder) {
            $mask = $this->rightsRepository->findMaximumRights(array_map(function (Role $role) {
                return $role->getRole();
            }, $token->getRoles()), $object);

            if ($mask & $decoder->getValue()) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return OrderedTool::class;
    }

    public function getSupportedActions()
    {
        //atm, null means "everything is supported... implement this later"
        return null;
    }
}
