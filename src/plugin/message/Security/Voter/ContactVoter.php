<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\MessageBundle\Security\Voter;

use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\MessageBundle\Entity\Contact\Contact;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ContactVoter extends AbstractVoter
{
    /**
     * @param Contact $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        // contacts are only visible by the owner
        if ($token->getUser() instanceof User && $object->getUser() && $token->getUser()->getId() === $object->getUser()->getId()) {
            return VoterInterface::ACCESS_GRANTED;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass()
    {
        return Contact::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
