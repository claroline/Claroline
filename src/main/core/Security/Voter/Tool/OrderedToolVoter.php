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

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\Tool\OrderedTool;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Checks if the current token can access a tool configured in a Workspace or Desktop
 * (it should also check for admin tools later).
 */
class OrderedToolVoter extends AbstractVoter
{
    /**
     * @param OrderedTool $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        if (!empty($object->getWorkspace())) {
            // let the workspace voter decide
            $isGranted = $this->isGranted([$object->getTool()->getName(), $attributes[0]], $object->getWorkspace());
        } else {
            // let the base tool voter decide
            $isGranted = $this->isGranted($attributes[0], $object->getTool());
        }

        if ($isGranted) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass(): string
    {
        return OrderedTool::class;
    }

    public function getSupportedActions(): ?array
    {
        //atm, null means "everything is supported... implement this later"
        return null;
    }
}
