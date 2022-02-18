<?php

namespace Claroline\TransferBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\TransferBundle\Entity\AbstractTransferFile;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

abstract class AbstractTransferFileVoter extends AbstractVoter
{
    /**
     * @param AbstractTransferFile $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        if ($object->getWorkspace() && $this->isGranted(['workspace', 'OPEN'], $object->getWorkspace())) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->hasAdminToolAccess($token, 'transfer')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
