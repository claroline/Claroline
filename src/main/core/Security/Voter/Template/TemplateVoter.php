<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter\Template;

use Claroline\CoreBundle\Entity\Template\Template;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class TemplateVoter extends AbstractVoter
{
    /**
     * @param Template $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::OPEN:
            case self::VIEW:
                return $this->hasAdminToolAccess($token, 'templates') ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED;

            case self::EDIT:
            case self::DELETE:
            case self::PATCH:
                if ($object->isSystem()) {
                    // system templates are managed through claroline updates, so nobody can modify them
                    return VoterInterface::ACCESS_DENIED;
                }

                return $this->hasAdminToolAccess($token, 'templates') ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return Template::class;
    }

    public function getSupportedActions()
    {
        return [self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
