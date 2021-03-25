<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\ForumBundle\Security;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class MessageVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE: return $this->checkCreate($token);
        }
    }

    public function checkCreate($token)
    {
        return $token->getUser() instanceof User ? VoterInterface::ACCESS_GRANTED : VoterInterface::ACCESS_DENIED;
    }

    public function getClass()
    {
        return 'Claroline\ForumBundle\Entity\Message';
    }

    public function getSupportedActions()
    {
        return [self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
