<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Security;

use Symfony\Component\Security\Core\User\UserChecker as BaseChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker extends BaseChecker
{
    public function checkPostAuth(UserInterface $user)
    {
        parent::checkPostAuth($user);
        //check if the platform is still valid
    }
}