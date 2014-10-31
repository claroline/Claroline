<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Controller\Oauth;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class SecurityController extends Controller
{
    /**
     * @Route("/oauth/v2/auth_login", name="claro_api_oauth_login")
     * @Template
     */
    public function loginAction(Request $request)
    {
        $lastUsername = $request->getSession()->get(SecurityContext::LAST_USERNAME);
        $user         = $this->get('claroline.manager.user_manager')->getUserByUsername($lastUsername);

        if ($user && !$user->isAccountNonExpired()) {
            return array(
                'last_username' => $lastUsername,
                'error'         => false,
                'is_expired'    => true
            );
        }

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
            'last_username' => $lastUsername,
            'error'         => $error,
            'is_expired'    => false
        );
    }

    /**
     * @Route("/oauth/v2/auth_login_check", name="claro_api_oauth_login_check")
     * @Template
     */
    public function loginCheckAction(Request $request)
    {
        // The security layer will intercept this request
    }
}
