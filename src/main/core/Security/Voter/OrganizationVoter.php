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

use Claroline\CoreBundle\Entity\Organization\Organization;
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

    public function checkPermission(TokenInterface $token, $object, array $attributes, array $options)
    {
        if ('create' === $this->config->getParameter('registration.organization_selection')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        if ($this->hasAdminToolAccess($token, 'community')) {
            return VoterInterface::ACCESS_GRANTED;
        }

        //not used in workspaces yet so no implementation
        return VoterInterface::ACCESS_DENIED;
    }

    public function getClass()
    {
        return Organization::class;
    }

    public function getSupportedActions()
    {
        return [self::CREATE, self::EDIT, self::DELETE, self::PATCH];
    }
}
