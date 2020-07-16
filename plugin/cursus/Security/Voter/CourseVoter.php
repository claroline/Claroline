<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CursusBundle\Security\Voter;

use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Claroline\CursusBundle\Entity\Course;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class CourseVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE: // EDIT right on tool
            case self::EDIT: // admin of organization | EDIT right on tool
            case self::DELETE: // admin of organization | EDIT right on tool
            case self::OPEN: // member of organization & OPEN right on tool
            case self::VIEW:
                return $this->hasAdminToolAccess($token, 'claroline_cursus_tool') ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return Course::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE];
    }
}
