<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter;

use Claroline\CoreBundle\Entity\Import\File;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ImportFileVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE: return $this->check($token, $object);
            case self::EDIT:   return $this->check($token, $object);
            case self::DELETE: return $this->check($token, $object);
            case self::PATCH:  return $this->check($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function check(TokenInterface $token, File $file)
    {
        //no real permission check right now, we'll have to discuss it later
        return VoterInterface::ACCESS_GRANTED;
    }

    public function getClass()
    {
        return File::class;
    }

    public function getSupportedActions()
    {
        return [self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
