<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FormaLibre\ReservationBundle\Security\Voter;

use Claroline\CoreBundle\Security\AbstractVoter;
use FormaLibre\ReservationBundle\Entity\ResourceRights;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @DI\Service
 * @DI\Tag("security.voter")
 */
class ResourceRightsVoter extends AbstractVoter
{
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        switch ($attributes[0]) {
            case self::CREATE:
            case self::PATCH:
                return $this->checkCreation($token);
            case self::OPEN:
            case self::VIEW:
            case self::EDIT:
            case self::DELETE:
                return $this->checkEdition($token, $object);
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }

    public function getClass()
    {
        return 'FormaLibre\ReservationBundle\Entity\ResourceRights';
    }

    public function getSupportedActions()
    {
        return[self::OPEN, self::VIEW, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }

    /**
     * @param TokenInterface $token
     *
     * @return int
     */
    private function checkCreation(TokenInterface $token)
    {
        return $this->hasAdminToolAccess($token, 'formalibre_reservation_tool') ?
            VoterInterface::ACCESS_GRANTED :
            VoterInterface::ACCESS_DENIED;
    }

    /**
     * @param TokenInterface $token
     * @param ResourceRights $resourceRights
     *
     * @return int
     */
    private function checkEdition(TokenInterface $token, ResourceRights $resourceRights)
    {
        $user = $token->getUser();
        $resource = $resourceRights->getResource();

        if (!$this->hasAdminToolAccess($token, 'formalibre_reservation_tool') || $user === 'anon.') {
            return VoterInterface::ACCESS_DENIED;
        }
        $userOrganizations = $user->getOrganizations();
        $resourceOrganizations = $resource->getOrganizations();

        foreach ($userOrganizations as $userOrga) {
            foreach ($resourceOrganizations as $resourceOrga) {
                if ($userOrga->getId() === $resourceOrga->getId()) {
                    return VoterInterface::ACCESS_GRANTED;
                }
            }
        }

        return VoterInterface::ACCESS_DENIED;
    }
}
