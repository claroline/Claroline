<?php

namespace Claroline\TransferBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;

abstract class AbstractTransferFileVoter extends AbstractVoter
{
    public function getSupportedActions(): array
    {
        return [self::OPEN, self::CREATE, self::EDIT, self::DELETE];
    }
}
