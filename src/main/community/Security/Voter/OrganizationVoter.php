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
    /** @var PlatformConfigurationHandler */
    private $config;

    public function __construct(PlatformConfigurationHandler $config)
    {
        $this->config = $config;
    }

    /**
     * @param Organization $object
     */
    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options): int
    {
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
                if ($token->getUser() instanceof User && $object->hasUser($token->getUser())) {
                    return VoterInterface::ACCESS_GRANTED;
                }
                break;
            case self::EDIT:
            case self::PATCH:
                if ($token->getUser() instanceof User && $this->isToolGranted('EDIT', 'community') && $object->hasManager($token->getUser())) {
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
