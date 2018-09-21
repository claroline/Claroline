<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Security\Voter\Tool\Home;

use Claroline\CoreBundle\Entity\Tab\HomeTab;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class HomeTabVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::EDIT:   return $this->check($token, $object);
            case self::DELETE: return $this->check($token, $object);
      }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function check(TokenInterface $token, HomeTab $object)
    {
        if (HomeTab::TYPE_ADMIN_DESKTOP === $object->getType() && !$this->isAdmin($token)) {
            return VoterInterface::ACCESS_DENIED;
        } else {
            if (HomeTab::TYPE_DESKTOP === $object->getType()) {
                if ($object->getUser() !== $token->getUser()) {
                    return VoterInterface::ACCESS_DENIED;
                }
            } elseif (HomeTab::TYPE_WORKSPACE === $object->getType()) {
                $workspace = $object->getWorkspace();

                return $this->isGranted(self::EDIT, $workspace);
            }
        }

        return VoterInterface::ACCESS_GRANTED;
    }

    public function getClass()
    {
        return HomeTab::class;
    }

    public function getSupportedActions()
    {
        return[self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
