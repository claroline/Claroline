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
    private ToolMaskDecoderManager $maskManager;
    private ToolRightsRepository $rightsRepository;

    public function __construct(
        ObjectManager $om,
        ToolMaskDecoderManager $maskManager
    ) {
        $this->maskManager = $maskManager;
        $this->rightsRepository = $om->getRepository(ToolRights::class);
    }

    /**
     * @param OrderedTool $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        // FIXME : admin bypass will not work
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
