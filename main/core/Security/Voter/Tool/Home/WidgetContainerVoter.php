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
use Claroline\CoreBundle\Entity\Widget\WidgetContainer;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class WidgetContainerVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                return $this->check($token, $object);
      }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function check(TokenInterface $token, WidgetContainer $object)
    {
        $homeTab = $object->getHomeTab();

        switch ($homeTab->getType()) {
            case HomeTab::TYPE_ADMIN_DESKTOP:
                if (!$this->isAdmin($token)) {
                    return VoterInterface::ACCESS_DENIED;
                }
                break;
            case HomeTab::TYPE_DESKTOP:
                if ($homeTab->getUser() !== $token->getUser()) {
                    return VoterInterface::ACCESS_DENIED;
                }
                break;
            case HomeTab::TYPE_WORKSPACE:
                return $this->isGranted(self::EDIT, $homeTab->getWorkspace()) ?
                    VoterInterface::ACCESS_GRANTED :
                    VoterInterface::ACCESS_DENIED;
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return WidgetContainer::class;
    }

    public function getSupportedActions()
    {
        return[self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
