<?php

namespace Claroline\CoreBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * A token for users that get authenticated because
 * their IP address has been white listed in the platform configuration.
 */
class IpToken extends UsernamePasswordToken
{
}
