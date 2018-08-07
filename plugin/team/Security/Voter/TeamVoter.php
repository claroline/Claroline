<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\TeamBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\VoterInterface;
use Claroline\CoreBundle\Security\Voter\AbstractVoter;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class TeamVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
            case self::PATCH:
                return $this->isGranted(['claroline_team_tool', 'edit'], $object->getWorkspace());
            case self::OPEN:
            case self::VIEW:
                return $this->isGranted(['claroline_team_tool', 'open'], $object->getWorkspace());
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return 'Claroline\TeamBundle\Entity\Team';
    }

    public function getSupportedActions()
    {
        return[self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
