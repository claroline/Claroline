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

use Claroline\AppBundle\Component\Context\ContextProvider;
use Claroline\AppBundle\Persistence\ObjectManager;
use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Component\Context\AccountContext;
use Claroline\CoreBundle\Component\Context\PublicContext;
use Claroline\CoreBundle\Component\Context\WorkspaceContext;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;
use Claroline\CoreBundle\Repository\Tool\ToolRightsRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Checks if the current token can access a tool configured in a Workspace or Desktop
 * (it should also check for admin tools later).
 */
class OrderedToolVoter extends AbstractVoter
{
    private ContextProvider $contextProvider;
    private ToolMaskDecoderManager $maskManager;
    private ToolRightsRepository $rightsRepository;

    public function __construct(
        ObjectManager $om,
        ContextProvider $contextProvider,
        ToolMaskDecoderManager $maskManager
    ) {
        $this->contextProvider = $contextProvider;
        $this->maskManager = $maskManager;
        $this->rightsRepository = $om->getRepository(ToolRights::class);
    }

    /**
     * @param OrderedTool $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        // No rights management for PublicContext for now
        if (PublicContext::getName() === $object->getContextName()) {
            if (self::OPEN === $attributes[0] || $this->isGranted('ROLE_HOME_MANAGER')) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        // No rights management for AccountContext for now
        if (AccountContext::getName() === $object->getContextName()) {
            if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        if (WorkspaceContext::getName() === $object->getContextName()) {
            $wsContext = $this->contextProvider->getContext(WorkspaceContext::getName());
            if ($this->isGranted(self::ADMINISTRATE, $wsContext->getObject($object->getContextId()))) {
                return VoterInterface::ACCESS_GRANTED;
            }
        }

        $decoder = $this->maskManager->getMaskDecoderByToolAndName($object->getName(), $attributes[0]);
        if ($decoder) {
            $mask = $this->rightsRepository->findMaximumRights($token->getRoleNames(), $object);

            if ($mask & $decoder->getValue()) {
                return VoterInterface::ACCESS_GRANTED;
            }

            return VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass(): string
    {
        return OrderedTool::class;
    }

    public function getSupportedActions(): ?array
    {
        // atm, null means "everything is supported... implement this later"
        return null;
    }
}
