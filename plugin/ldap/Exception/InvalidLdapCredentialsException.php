<?php
/**
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Date: 4/3/17
 */

namespace Claroline\LdapBundle\Exception;

use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class InvalidLdapCredentialsException extends BadCredentialsException
{
    public function getMessageKey()
    {
        return 'Invalid LDAP credentials.';
    }
}
