<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\DropZoneBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class DropzoneToolVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        return $this->hasAdminToolAccess($token, 'main_settings') ?
            VoterInterface::ACCESS_GRANTED :
            VoterInterface::ACCESS_DENIED;
    }

    public function getClass()
    {
        return 'Claroline\DropZoneBundle\Entity\DropzoneTool';
    }

    public function getSupportedActions()
    {
        return [self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
