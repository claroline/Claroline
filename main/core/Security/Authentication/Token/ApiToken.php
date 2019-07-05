<?php

namespace Claroline\CoreBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * A token for users that get authenticated because they could provide their api key.
 */
class ApiToken extends UsernamePasswordToken
{
}
