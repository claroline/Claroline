<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CommunityBundle\Security\Voter;

use Claroline\AppBundle\Security\Voter\AbstractVoter;
use Claroline\CoreBundle\Entity\Organization\Organization;
use Claroline\CoreBundle\Entity\User;
use Claroline\CoreBundle\Library\Configuration\PlatformConfigurationHandler;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class OrganizationVoter extends AbstractVoter
{
    public function __construct(
        private readonly PlatformConfigurationHandler $config
    ) {
    }

    /**
     * @param Organization $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
        /** @var User|string|null $currentUser */
        $currentUser = $token->getUser();

        switch ($attributes[0]) {
            case self::CREATE:
                if ('create' === $this->config->getParameter('registration.organization_selection')) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                if ($this->isToolGranted('EDIT', 'community')) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
            case self::OPEN:
                if ($currentUser instanceof User && $currentUser->hasOrganization($object)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
            case self::EDIT:
            case self::PATCH:
                if ($currentUser instanceof User && $this->isToolGranted('EDIT', 'community') && $currentUser->hasOrganization($object, true)) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
        }

        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass(): string
    {
        return Organization::class;
    }

    public function getSupportedActions(): array
    {
        return [self::CREATE, self::OPEN, self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
