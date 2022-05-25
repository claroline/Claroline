<?php

namespace Claroline\TransferBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;

abstract class AbstractTransferFileVoter extends AbstractVoter
{
    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
