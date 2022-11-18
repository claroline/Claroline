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
use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\Tool\Tool;
use Claroline\CoreBundle\Entity\Tool\ToolRights;
use Claroline\CoreBundle\Manager\Tool\ToolMaskDecoderManager;
use Claroline\CoreBundle\Repository\Tool\ToolRightsRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * ATTENTION : it does not work with Workspace tools. For them, it's the OrderedToolVoter or WorkspaceVoter.
 */
class ToolVoter extends AbstractVoter
{
    /** @var ToolMaskDecoderManager */
    private $maskManager;

    /** @var ToolRightsRepository */
    private $rightsRepository;

    public function __construct(
        ObjectManager $om,
        ToolMaskDecoderManager $maskManager
    ) {
        $this->maskManager = $maskManager;
        $this->rightsRepository = $om->getRepository(ToolRights::class);
    }

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        if ($this->isAdmin($token)) {
            return VoterInterface::ACCESS_GRANTED;
        }

        $decoder = $this->maskManager->getMaskDecoderByToolAndName($object, $attributes[0]);
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
        return Tool::class;
    }

    public function getSupportedActions(): ?array
    {
        //atm, null means "everything is supported... implement this later"
        return null;
    }
}
