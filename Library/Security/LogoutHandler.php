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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;

class LogoutHandler extends SessionLogoutHandler
{
    /**
     * {@inheritdoc}
     *
     * PHP 5.4 versions prior to 5.4.11 suffer from a bug in SessionHandler
     * (see https://bugs.php.net/bug.php?id=63379). The only workaround for
     * those versions is to avoid session invalidation (see
     * https://github.com/symfony/symfony/issues/5868). Here the workaround
     * will be applied only if the PHP version is buggy (as opposed to a
     * permanent "invalidate_session: false" in the firewall configuration).
     *
     * @param Request        $request
     * @param Response       $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if (version_compare(phpversion(), '5.4.11', '>=')) {
            parent::logout($request, $response, $token);
        }
    }
}
